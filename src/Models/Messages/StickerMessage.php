<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models\Messages;

use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\States\MessageState;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int                                          $id
 * @property string                                       $wamid
 * @property string                                       $profile
 * @property string                                       $from
 * @property MessageType|string $type
 * @property object                                       $message
 * @property int                                          $timestamp
 * @property MessageState      $state
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 *
 * @method static Builder<static>|StickerMessage chat(string $from)
 * @method static Builder<static>|StickerMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|StickerMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|StickerMessage findByFrom(string $from)
 * @method static Builder<static>|StickerMessage findByWamid(string $wamid)
 * @method static Builder<static>|StickerMessage lastMessage()
 * @method static Builder<static>|StickerMessage lastTextMessage()
 * @method static Builder<static>|StickerMessage me()
 * @method static Builder<static>|StickerMessage newModelQuery()
 * @method static Builder<static>|StickerMessage newQuery()
 * @method static Builder<static>|StickerMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|StickerMessage orWhereState(string $column, $states)
 * @method static Builder<static>                query()
 * @method static Builder<static>|StickerMessage whereFrom($value)
 * @method static Builder<static>|StickerMessage whereId($value)
 * @method static Builder<static>|StickerMessage whereMessage($value)
 * @method static Builder<static>|StickerMessage whereNotState(string $column, $states)
 * @method static Builder<static>|StickerMessage whereProfile($value)
 * @method static Builder<static>|StickerMessage whereState($value)
 * @method static Builder<static>|StickerMessage whereTimestamp($value)
 * @method static Builder<static>|StickerMessage whereType($value)
 * @method static Builder<static>|StickerMessage whereWamid($value)
 * @method static Builder<static>|StickerMessage you()
 */
final class StickerMessage extends Message {}
