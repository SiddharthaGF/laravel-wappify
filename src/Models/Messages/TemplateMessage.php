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
 * @method static Builder<static>|TemplateMessage chat(string $from)
 * @method static Builder<static>|TemplateMessage childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|TemplateMessage childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|TemplateMessage findByFrom(string $from)
 * @method static Builder<static>|TemplateMessage findByWamid(string $wamid)
 * @method static Builder<static>|TemplateMessage lastMessage()
 * @method static Builder<static>|TemplateMessage lastTextMessage()
 * @method static Builder<static>|TemplateMessage me()
 * @method static Builder<static>|TemplateMessage newModelQuery()
 * @method static Builder<static>|TemplateMessage newQuery()
 * @method static Builder<static>|TemplateMessage orWhereNotState(string $column, $states)
 * @method static Builder<static>|TemplateMessage orWhereState(string $column, $states)
 * @method static Builder<static>                 query()
 * @method static Builder<static>|TemplateMessage whereFrom($value)
 * @method static Builder<static>|TemplateMessage whereId($value)
 * @method static Builder<static>|TemplateMessage whereMessage($value)
 * @method static Builder<static>|TemplateMessage whereNotState(string $column, $states)
 * @method static Builder<static>|TemplateMessage whereProfile($value)
 * @method static Builder<static>|TemplateMessage whereState($value)
 * @method static Builder<static>|TemplateMessage whereTimestamp($value)
 * @method static Builder<static>|TemplateMessage whereType($value)
 * @method static Builder<static>|TemplateMessage whereWamid($value)
 * @method static Builder<static>|TemplateMessage you()
 */
final class TemplateMessage extends Message {}
