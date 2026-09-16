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
 * @method static Builder<static>|DocumentMessage chat(string $from)
 * @method static Builder<static>|DocumentMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|DocumentMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|DocumentMessage findByFrom(string $from)
 * @method static Builder<static>|DocumentMessage findByWamid(string $wamid)
 * @method static Builder<static>|DocumentMessage lastMessage()
 * @method static Builder<static>|DocumentMessage lastTextMessage()
 * @method static Builder<static>|DocumentMessage me()
 * @method static Builder<static>|DocumentMessage newModelQuery()
 * @method static Builder<static>|DocumentMessage newQuery()
 * @method static Builder<static>|DocumentMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|DocumentMessage orWhereState(string $column, $states)
 * @method static Builder<static>                 query()
 * @method static Builder<static>|DocumentMessage whereFrom($value)
 * @method static Builder<static>|DocumentMessage whereId($value)
 * @method static Builder<static>|DocumentMessage whereMessage($value)
 * @method static Builder<static>|DocumentMessage whereNotState(string $column, $states)
 * @method static Builder<static>|DocumentMessage whereProfile($value)
 * @method static Builder<static>|DocumentMessage whereState($value)
 * @method static Builder<static>|DocumentMessage whereTimestamp($value)
 * @method static Builder<static>|DocumentMessage whereType($value)
 * @method static Builder<static>|DocumentMessage whereWamid($value)
 * @method static Builder<static>|DocumentMessage you()
 */
final class DocumentMessage extends Message {}
