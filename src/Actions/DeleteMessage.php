<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Actions;

use AiluraCode\Wappify\Models\Whatsapp;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Delete a message row, optionally with its media attachments.
 *
 * Media is deleted before the row inside a transaction, so a media
 * failure rolls the row back. Missing rows throw for the caller to
 * map to 404.
 */
final class DeleteMessage
{
    public function __construct(
        private readonly string $id,
        private readonly bool   $withMedia = false,
    ) {}

    /**
     * @throws Throwable
     *
     * @return array{message: string}
     */
    public function __invoke(): array
    {
        $whatsapp = Whatsapp::find($this->id);

        if (! $whatsapp instanceof Whatsapp) {
            throw new ModelNotFoundException("WhatsApp row $this->id not found.");
        }

        if ($this->withMedia) {
            DB::transaction(static fn () => $whatsapp->deleteWithMedia());

            return ['message' => 'Whatsapp deleted with media'];
        }

        $whatsapp->delete();

        return ['message' => 'Whatsapp deleted'];
    }
}
