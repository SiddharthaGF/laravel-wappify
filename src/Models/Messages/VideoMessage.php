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
 * @method static Builder<static>|VideoMessage chat(string $from)
 * @method static Builder<static>|VideoMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|VideoMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|VideoMessage findByFrom(string $from)
 * @method static Builder<static>|VideoMessage findByWamid(string $wamid)
 * @method static Builder<static>|VideoMessage lastMessage()
 * @method static Builder<static>|VideoMessage lastTextMessage()
 * @method static Builder<static>|VideoMessage me()
 * @method static Builder<static>|VideoMessage newModelQuery()
 * @method static Builder<static>|VideoMessage newQuery()
 * @method static Builder<static>|VideoMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|VideoMessage orWhereState(string $column, $states)
 * @method static Builder<static>              query()
 * @method static Builder<static>|VideoMessage whereFrom($value)
 * @method static Builder<static>|VideoMessage whereId($value)
 * @method static Builder<static>|VideoMessage whereMessage($value)
 * @method static Builder<static>|VideoMessage whereNotState(string $column, $states)
 * @method static Builder<static>|VideoMessage whereProfile($value)
 * @method static Builder<static>|VideoMessage whereState($value)
 * @method static Builder<static>|VideoMessage whereTimestamp($value)
 * @method static Builder<static>|VideoMessage whereType($value)
 * @method static Builder<static>|VideoMessage whereWamid($value)
 * @method static Builder<static>|VideoMessage you()
 */
final class VideoMessage extends Message {}
