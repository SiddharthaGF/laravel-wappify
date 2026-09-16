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
 * @method static Builder<static>|LocationMessage chat(string $from)
 * @method static Builder<static>|LocationMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|LocationMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|LocationMessage findByFrom(string $from)
 * @method static Builder<static>|LocationMessage findByWamid(string $wamid)
 * @method static Builder<static>|LocationMessage lastMessage()
 * @method static Builder<static>|LocationMessage lastTextMessage()
 * @method static Builder<static>|LocationMessage me()
 * @method static Builder<static>|LocationMessage newModelQuery()
 * @method static Builder<static>|LocationMessage newQuery()
 * @method static Builder<static>|LocationMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|LocationMessage orWhereState(string $column, $states)
 * @method static Builder<static>                 query()
 * @method static Builder<static>|LocationMessage whereFrom($value)
 * @method static Builder<static>|LocationMessage whereId($value)
 * @method static Builder<static>|LocationMessage whereMessage($value)
 * @method static Builder<static>|LocationMessage whereNotState(string $column, $states)
 * @method static Builder<static>|LocationMessage whereProfile($value)
 * @method static Builder<static>|LocationMessage whereState($value)
 * @method static Builder<static>|LocationMessage whereTimestamp($value)
 * @method static Builder<static>|LocationMessage whereType($value)
 * @method static Builder<static>|LocationMessage whereWamid($value)
 * @method static Builder<static>|LocationMessage you()
 */
final class LocationMessage extends Message {}
