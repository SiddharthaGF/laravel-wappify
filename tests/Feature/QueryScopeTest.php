<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Tests\Feature;

use AiluraCode\Wappify\Models\Whatsapp;
use AiluraCode\Wappify\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

/**
 * The named scopes are documented public API, but only `chat` is reached by the
 * controller tests. They were also left behind by a planned Laravel 13 `#[Scope]`
 * migration, which relies on the free function names these scopes currently
 * expose — so the names are pinned here.
 */
final class QueryScopeTest extends TestCase
{
    use DatabaseMigrations;

    public function test_chat_filters_on_sender_and_orders_by_timestamp_descending(): void
    {
        $this->seedRows();

        $this->assertSame(
            ['wamid.a', 'wamid.c'],
            Whatsapp::chat('111')->pluck('wamid')->all()
        );
    }

    public function test_find_by_from_filters_on_sender(): void
    {
        $this->seedRows();

        $scoped = Whatsapp::findByFrom('111');

        $this->assertSame(2, $scoped->count());
        $this->assertTrue(Whatsapp::findByFrom('111')->where('wamid', 'wamid.a')->exists());
        $this->assertTrue(Whatsapp::findByFrom('111')->where('wamid', 'wamid.c')->exists());
    }

    public function test_find_by_wamid_returns_the_matching_row(): void
    {
        $this->seedRows();

        $row = Whatsapp::findByWamid('wamid.b==');

        if (! $row instanceof Whatsapp) {
            $this->fail('findByWamid did not resolve wamid.b==');
        }

        $this->assertSame('wamid.b==', $row->wamid);
    }

    public function test_last_message_orders_by_timestamp_descending(): void
    {
        $this->seedRows();

        $row = Whatsapp::lastMessage();

        if (! $row instanceof Whatsapp) {
            $this->fail('lastMessage did not resolve a row');
        }

        $this->assertSame('wamid.b==', $row->wamid);
    }

    public function test_last_text_message_skips_other_types(): void
    {
        $this->seedRows();

        $row = Whatsapp::lastTextMessage();

        if (! $row instanceof Whatsapp) {
            $this->fail('lastTextMessage did not resolve a row');
        }

        $this->assertSame('wamid.a', $row->wamid);
    }

    /**
     * `me`/`you` compare the wamid with a `LIKE` marker, so the pinned SQL must
     * keep the marker out of the wildcard parse.
     */
    public function test_me_and_you_pin_their_like_marker(): void
    {
        $me = Whatsapp::me();
        $you = Whatsapp::you();

        $this->assertStringContainsString('"wamid" LIKE ?', $me->toSql());
        $this->assertStringContainsString('"wamid" NOT LIKE ?', $you->toSql());
        $this->assertSame(['%=='], $me->getBindings());
        $this->assertSame(['%=='], $you->getBindings());
    }

    public function test_me_and_you_split_on_the_wamid_marker(): void
    {
        $this->seedRows();

        $this->assertSame(1, Whatsapp::me()->count());
        $this->assertTrue(Whatsapp::me()->where('wamid', 'wamid.b==')->exists());

        $this->assertSame(2, Whatsapp::you()->count());
        $this->assertTrue(Whatsapp::you()->where('wamid', 'wamid.a')->exists());
        $this->assertTrue(Whatsapp::you()->where('wamid', 'wamid.c')->exists());
        $this->assertFalse(Whatsapp::you()->where('wamid', 'wamid.b==')->exists());
    }

    private function insertRow(string $wamid, string $from, string $type, int $timestamp): void
    {
        DB::table('whatsapp')->insert([
            'wamid' => $wamid,
            'profile' => 'default',
            'from' => $from,
            'type' => $type,
            'message' => json_encode(['type' => $type]),
            'timestamp' => $timestamp,
            'state' => 'waiting',
        ]);
    }

    /**
     * Three rows: a text from 111 with no marker, a video from 222 with the
     * `==` marker (our own send), and an older text from 111.
     */
    private function seedRows(): void
    {
        $this->insertRow('wamid.a', '111', 'text', 100);
        $this->insertRow('wamid.b==', '222', 'video', 200);
        $this->insertRow('wamid.c', '111', 'text', 50);
    }
}
