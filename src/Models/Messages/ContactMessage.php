<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models\Messages;

use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\States\MessageState;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Owns both the `contact` and `contacts` aliases.
 *
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
 * @method static Builder<static>|ContactMessage chat(string $from)
 * @method static Builder<static>|ContactMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|ContactMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|ContactMessage findByFrom(string $from)
 * @method static Builder<static>|ContactMessage findByWamid(string $wamid)
 * @method static Builder<static>|ContactMessage lastMessage()
 * @method static Builder<static>|ContactMessage lastTextMessage()
 * @method static Builder<static>|ContactMessage me()
 * @method static Builder<static>|ContactMessage newModelQuery()
 * @method static Builder<static>|ContactMessage newQuery()
 * @method static Builder<static>|ContactMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|ContactMessage orWhereState(string $column, $states)
 * @method static Builder<static>                query()
 * @method static Builder<static>|ContactMessage whereFrom($value)
 * @method static Builder<static>|ContactMessage whereId($value)
 * @method static Builder<static>|ContactMessage whereMessage($value)
 * @method static Builder<static>|ContactMessage whereNotState(string $column, $states)
 * @method static Builder<static>|ContactMessage whereProfile($value)
 * @method static Builder<static>|ContactMessage whereState($value)
 * @method static Builder<static>|ContactMessage whereTimestamp($value)
 * @method static Builder<static>|ContactMessage whereType($value)
 * @method static Builder<static>|ContactMessage whereWamid($value)
 * @method static Builder<static>|ContactMessage you()
 */
final class ContactMessage extends Message {}
