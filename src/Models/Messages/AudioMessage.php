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
 * @method static Builder<static>|AudioMessage chat(string $from)
 * @method static Builder<static>|AudioMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|AudioMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|AudioMessage findByFrom(string $from)
 * @method static Builder<static>|AudioMessage findByWamid(string $wamid)
 * @method static Builder<static>|AudioMessage lastMessage()
 * @method static Builder<static>|AudioMessage lastTextMessage()
 * @method static Builder<static>|AudioMessage me()
 * @method static Builder<static>|AudioMessage newModelQuery()
 * @method static Builder<static>|AudioMessage newQuery()
 * @method static Builder<static>|AudioMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|AudioMessage orWhereState(string $column, $states)
 * @method static Builder<static>              query()
 * @method static Builder<static>|AudioMessage whereFrom($value)
 * @method static Builder<static>|AudioMessage whereId($value)
 * @method static Builder<static>|AudioMessage whereMessage($value)
 * @method static Builder<static>|AudioMessage whereNotState(string $column, $states)
 * @method static Builder<static>|AudioMessage whereProfile($value)
 * @method static Builder<static>|AudioMessage whereState($value)
 * @method static Builder<static>|AudioMessage whereTimestamp($value)
 * @method static Builder<static>|AudioMessage whereType($value)
 * @method static Builder<static>|AudioMessage whereWamid($value)
 * @method static Builder<static>|AudioMessage you()
 */
final class AudioMessage extends Message {}
