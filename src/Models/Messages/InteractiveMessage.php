<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models\Messages;

use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\States\MessageState;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Mirrors the interactive subtype accessor exposed by the legacy DTO.
 *
 * @property int                $id
 * @property string             $wamid
 * @property string             $profile
 * @property string             $from
 * @property MessageType|string $type
 * @property object             $message
 * @property int                $timestamp
 * @property MessageState       $state
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 *
 * @method static Builder<static>|InteractiveMessage chat(string $from)
 * @method static Builder<static>|InteractiveMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|InteractiveMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|InteractiveMessage findByFrom(string $from)
 * @method static Builder<static>|InteractiveMessage findByWamid(string $wamid)
 * @method static Builder<static>|InteractiveMessage lastMessage()
 * @method static Builder<static>|InteractiveMessage lastTextMessage()
 * @method static Builder<static>|InteractiveMessage me()
 * @method static Builder<static>|InteractiveMessage newModelQuery()
 * @method static Builder<static>|InteractiveMessage newQuery()
 * @method static Builder<static>|InteractiveMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|InteractiveMessage orWhereState(string $column, $states)
 * @method static Builder<static>                    query()
 * @method static Builder<static>|InteractiveMessage whereFrom($value)
 * @method static Builder<static>|InteractiveMessage whereId($value)
 * @method static Builder<static>|InteractiveMessage whereMessage($value)
 * @method static Builder<static>|InteractiveMessage whereNotState(string $column, $states)
 * @method static Builder<static>|InteractiveMessage whereProfile($value)
 * @method static Builder<static>|InteractiveMessage whereState($value)
 * @method static Builder<static>|InteractiveMessage whereTimestamp($value)
 * @method static Builder<static>|InteractiveMessage whereType($value)
 * @method static Builder<static>|InteractiveMessage whereWamid($value)
 * @method static Builder<static>|InteractiveMessage you()
 */
final class InteractiveMessage extends Message
{
    public function getInteractiveType(): string
    {
        $type = $this->getMessage()->type ?? null;

        return is_string($type) ? $type : '';
    }
}
