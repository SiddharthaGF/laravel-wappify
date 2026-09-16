<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models\Messages;

use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\States\MessageState;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
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
 * @method static Builder<static>|ImageMessage chat(string $from)
 * @method static Builder<static>|ImageMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|ImageMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|ImageMessage findByFrom(string $from)
 * @method static Builder<static>|ImageMessage findByWamid(string $wamid)
 * @method static Builder<static>|ImageMessage lastMessage()
 * @method static Builder<static>|ImageMessage lastTextMessage()
 * @method static Builder<static>|ImageMessage me()
 * @method static Builder<static>|ImageMessage newModelQuery()
 * @method static Builder<static>|ImageMessage newQuery()
 * @method static Builder<static>|ImageMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|ImageMessage orWhereState(string $column, $states)
 * @method static Builder<static>              query()
 * @method static Builder<static>|ImageMessage whereFrom($value)
 * @method static Builder<static>|ImageMessage whereId($value)
 * @method static Builder<static>|ImageMessage whereMessage($value)
 * @method static Builder<static>|ImageMessage whereNotState(string $column, $states)
 * @method static Builder<static>|ImageMessage whereProfile($value)
 * @method static Builder<static>|ImageMessage whereState($value)
 * @method static Builder<static>|ImageMessage whereTimestamp($value)
 * @method static Builder<static>|ImageMessage whereType($value)
 * @method static Builder<static>|ImageMessage whereWamid($value)
 * @method static Builder<static>|ImageMessage you()
 */
final class ImageMessage extends Message {}
