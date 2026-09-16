<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Enums\MessageStatusType;
use AiluraCode\Wappify\Enums\StateNames;
use AiluraCode\Wappify\Models\Messages\TextMessage;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\States\Delivered;
use AiluraCode\Wappify\States\MessageState;
use AiluraCode\Wappify\States\Read;
use AiluraCode\Wappify\States\Sent;
use AiluraCode\Wappify\States\Waiting;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;

final class MessageLifecycleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @throws CouldNotPerformTransition
     */
    public function test_forward_transitions_apply_in_order(): void
    {
        $this->insertMessage('wamid.lifecycle', 'waiting');
        $message = $this->find('wamid.lifecycle');

        $message->state->transitionTo(Sent::class);
        $sendState = $message->state;
        assert($sendState instanceof MessageState);
        $this->assertInstanceOf(Sent::class, $sendState);

        $message->state->transitionTo(Delivered::class);
        $deliveredState = $message->state;
        assert($deliveredState instanceof MessageState);
        $this->assertInstanceOf(Delivered::class, $deliveredState);

        $message->state->transitionTo(Read::class);
        $readState = $message->state;
        assert($readState instanceof MessageState);
        $this->assertInstanceOf(Read::class, $readState);

        $this->assertSame('read', DB::table('whatsapp')
            ->where('wamid', 'wamid.lifecycle')
            ->value('state'));
    }

    public function test_model_instantiation_defaults_to_waiting(): void
    {
        $model = new TextMessage([
            'type' => 'text',
            'message' => (object) ['body' => 'hello'],
        ]);

        $this->assertInstanceOf(Waiting::class, $model->state);
    }

    public function test_read_is_terminal(): void
    {
        $this->insertMessage('wamid.read-terminal', 'read');
        $message = $this->find('wamid.read-terminal');

        $this->expectException(CouldNotPerformTransition::class);
        $message->state->transitionTo(Sent::class);
    }

    public function test_row_with_default_state_casts_to_waiting(): void
    {
        $this->insertMessage('wamid.default');

        $state = $this->find('wamid.default')->state;

        $this->assertInstanceOf(Waiting::class, $state);
        $this->assertSame(MessageStatusType::WAITING->value, $state->getValue());
    }

    public function test_skipping_a_state_is_rejected(): void
    {
        $this->insertMessage('wamid.invalid', 'waiting');
        $message = $this->find('wamid.invalid');

        $this->expectException(CouldNotPerformTransition::class);
        $message->state->transitionTo(Read::class);
    }

    public function test_state_names_literals_match_state_names_holder(): void
    {
        $this->assertSame(StateNames::WAITING, Waiting::$name);
        $this->assertSame(StateNames::SENT, Sent::$name);
        $this->assertSame(StateNames::DELIVERED, Delivered::$name);
        $this->assertSame(StateNames::READ, Read::$name);
    }

    public function test_state_names_match_message_status_values(): void
    {
        $this->assertSame(MessageStatusType::WAITING->value, Waiting::$name);
        $this->assertSame(MessageStatusType::SENT->value, Sent::$name);
        $this->assertSame(MessageStatusType::DELIVERED->value, Delivered::$name);
        $this->assertSame(MessageStatusType::READ->value, Read::$name);
    }

    private function find(string $wamid): Whatsapp
    {
        $row = Whatsapp::query()->where('wamid', $wamid)->first();

        if (! $row instanceof Whatsapp) {
            $this->fail('Missing Whatsapp row for wamid ' . $wamid);
        }

        return $row;
    }

    private function insertMessage(string $wamid, ?string $state = null): void
    {
        $row = [
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => 'text',
            'message' => json_encode(['body' => 'hello']),
            'timestamp' => 1714177199,
        ];

        if ($state !== null) {
            $row['state'] = $state;
        }

        DB::table('whatsapp')->insert($row);
    }
}
