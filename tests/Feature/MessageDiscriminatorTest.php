<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Exceptions\UnknownMessageTypeException;
use AiluraCode\Wappify\Models\Messages\AudioMessage as AudioMessageModel;
use AiluraCode\Wappify\Models\Messages\ContactMessage as ContactMessageModel;
use AiluraCode\Wappify\Models\Messages\DocumentMessage as DocumentMessageModel;
use AiluraCode\Wappify\Models\Messages\ImageMessage as ImageMessageModel;
use AiluraCode\Wappify\Models\Messages\InteractiveMessage as InteractiveMessageModel;
use AiluraCode\Wappify\Models\Messages\LocationMessage as LocationMessageModel;
use AiluraCode\Wappify\Models\Messages\StickerMessage as StickerMessageModel;
use AiluraCode\Wappify\Models\Messages\TemplateMessage as TemplateMessageModel;
use AiluraCode\Wappify\Models\Messages\TextMessage as TextMessageModel;
use AiluraCode\Wappify\Models\Messages\VideoMessage as VideoMessageModel;
use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Support\PayloadMapper;
use AiluraCode\Wappify\Tests\TestCase;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

final class MessageDiscriminatorTest extends TestCase
{
    use DatabaseMigrations;

    public function test_child_query_is_scoped_and_parent_query_is_unfiltered(): void
    {
        $this->insertRow('wamid.text', 'text');
        $this->insertRow('wamid.video', 'video');

        $this->assertSame(1, DB::table('whatsapp')->where('type', 'text')->count());
        $this->assertSame('wamid.text', DB::table('whatsapp')->where('type', 'text')->value('wamid'));
        $this->assertSame(1, DB::table('whatsapp')->where('type', 'video')->count());
        $this->assertSame(2, DB::table('whatsapp')->count());
    }

    public function test_contact_aliases_share_one_child(): void
    {
        $this->insertRow('wamid.contact', 'contact');
        $this->insertRow('wamid.contacts', 'contacts');

        $this->assertInstanceOf(ContactMessageModel::class, $this->findWhatsapp('wamid.contact'));
        $this->assertInstanceOf(ContactMessageModel::class, $this->findWhatsapp('wamid.contacts'));
    }

    /**
     * @throws UnknownMessageTypeException
     */
    public function test_every_alias_hydrates_its_typed_child(): void
    {
        $map = $this->aliasMap();

        $this->assertCount(11, $map);

        foreach ($map as $alias => $expected) {
            $wamid = 'wamid.' . $alias;
            $this->insertRow($wamid, $alias);

            $row = $this->findWhatsapp($wamid);

            $this->assertInstanceOf($expected, $row, 'alias ' . $alias . ' resolved to ' . $row::class);
            $this->assertSame(MessageType::from($alias), $row->getType(), 'alias ' . $alias);
        }
    }

    /**
     * @throws Exception
     */
    public function test_factory_builds_unsaved_typed_child(): void
    {
        $payload = (string) (file_get_contents(__DIR__ . '/../../stubs/messageText.stub.json'));

        $model = PayloadMapper::toModel(PayloadMapper::fromJson($payload));

        $this->assertInstanceOf(VideoMessageModel::class, $model);
        $this->assertFalse($model->exists);
    }

    public function test_template_resolves_to_template_child(): void
    {
        $this->insertRow('wamid.template', 'template');

        $this->assertInstanceOf(TemplateMessageModel::class, $this->findWhatsapp('wamid.template'));
    }

    public function test_unmapped_alias_falls_back_to_base_model_with_raw_string_type(): void
    {
        $this->insertRow('wamid.reaction', 'reaction', ['emoji' => 'thumbs-up']);

        $row = $this->findWhatsapp('wamid.reaction');

        $this->assertSame(Whatsapp::class, $row::class);
        $this->assertSame('reaction', $row->type);

        $this->expectException(UnknownMessageTypeException::class);
        $row->getType();
    }

    /**
     * Every remaining MessageType alias mapped to its expected typed child.
     *
     * @return array<string, class-string>
     */
    private function aliasMap(): array
    {
        return [
            'text' => TextMessageModel::class,
            'image' => ImageMessageModel::class,
            'video' => VideoMessageModel::class,
            'audio' => AudioMessageModel::class,
            'document' => DocumentMessageModel::class,
            'sticker' => StickerMessageModel::class,
            'contact' => ContactMessageModel::class,
            'contacts' => ContactMessageModel::class,
            'location' => LocationMessageModel::class,
            'interactive' => InteractiveMessageModel::class,
            'template' => TemplateMessageModel::class,
        ];
    }

    private function findWhatsapp(string $wamid): Whatsapp
    {
        $row = Whatsapp::query()->where('wamid', $wamid)->first();

        if (! $row instanceof Whatsapp) {
            $this->fail('Missing WhatsApp row for wamid ' . $wamid);
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function insertRow(string $wamid, string $type, array $payload = []): void
    {
        DB::table('whatsapp')->insert([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => '593960800736',
            'type' => $type,
            'message' => json_encode($payload === [] ? ['type' => $type] : $payload),
            'timestamp' => 1714177199,
        ]);
    }
}
