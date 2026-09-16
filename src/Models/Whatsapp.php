<?php

declare(strict_types=1);

namespace AiluraCode\Wappify\Models;

use AiluraCode\Wappify\Casts\CastsMessageType;
use AiluraCode\Wappify\Concern\IsMessageable;
use AiluraCode\Wappify\Concern\IsTransformable;
use AiluraCode\Wappify\Concern\IsValidable;
use AiluraCode\Wappify\Contracts\ShouldMessage;
use AiluraCode\Wappify\Enums\MessageType;
use AiluraCode\Wappify\Models\Messages\AudioMessage;
use AiluraCode\Wappify\Models\Messages\ContactMessage;
use AiluraCode\Wappify\Models\Messages\DocumentMessage;
use AiluraCode\Wappify\Models\Messages\ImageMessage;
use AiluraCode\Wappify\Models\Messages\InteractiveMessage;
use AiluraCode\Wappify\Models\Messages\LocationMessage;
use AiluraCode\Wappify\Models\Messages\Message;
use AiluraCode\Wappify\Models\Messages\StickerMessage;
use AiluraCode\Wappify\Models\Messages\TemplateMessage;
use AiluraCode\Wappify\Models\Messages\TextMessage;
use AiluraCode\Wappify\Models\Messages\VideoMessage;
use AiluraCode\Wappify\States\MessageState;
use BackedEnum;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Parental\HasChildren;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\ModelStates\HasStates;
use stdClass;

/**
 * @property int                $id
 * @property string             $wamid
 * @property string             $profile
 * @property string             $from
 * @property MessageType|string $type
 * @property stdClass           $message
 * @property int                $timestamp
 * @property MessageState       $state
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 *
 * @method static Builder<static>                   chat(string $from)
 * @method static Builder<static>|Whatsapp          childrenWith(array<int|string, mixed> $relations)
 * @method static Builder<static>|Whatsapp          childrenWithCount(array<int|string, mixed> $relations)
 * @method static Builder<static>|Whatsapp          findByFrom(string $from)
 * @method static Builder<static>|Whatsapp          findByWamid(string $wamid)
 * @method static Builder<static>|Whatsapp          lastMessage()
 * @method static Builder<static>|Whatsapp          lastTextMessage()
 * @method static Builder<static>                   me()
 * @method static Builder<static>|Whatsapp          newModelQuery()
 * @method static Builder<static>|Whatsapp          newQuery()
 * @method static Builder<static>|Whatsapp          orWhereNotState(string $column, $states)
 * @method static Builder<static>|Whatsapp          orWhereState(string $column, $states)
 * @method static Builder<static>                   query()
 * @method static Builder<static>|Whatsapp          whereFrom($value)
 * @method static Builder<static>|Whatsapp          whereId($value)
 * @method static Builder<static>|Whatsapp          whereMessage($value)
 * @method static Builder<static>|Whatsapp          whereNotState(string $column, $states)
 * @method static Builder<static>|Whatsapp          whereProfile($value)
 * @method static Builder<static>|Whatsapp          whereState($value)
 * @method static Builder<static>|Whatsapp          whereTimestamp($value)
 * @method static Builder<static>|Whatsapp          whereType($value)
 * @method static Builder<static>|Whatsapp          whereWamid($value)
 * @method static Builder<static>                   you()
 * @method static static|null                       find(mixed $id, array<int, string>|string $columns = ['*'])
 * @method static LengthAwarePaginator<int, static> paginate(int|null $perPage = null, array<int, string> $columns = ['*'], string $pageName = 'page', int|null $page = null)
 */
class Whatsapp extends Model implements HasMedia, ShouldMessage
{
    use HasChildren;
    use HasStates;
    use InteractsWithMedia;
    use IsMessageable;
    use IsTransformable;
    use IsValidable;

    public $timestamps = false;

    /** @var array<int, string> */
    protected $fillable = [
        'wamid',
        'profile',
        'from',
        'type',
        'message',
        'timestamp',
        'state',
    ];

    protected $table = 'whatsapp';

    /**
     * Scope a query to only include messages from a specific number.
     */
    public static function scopeFindByFrom(QueryBuilder $query, string $from): QueryBuilder
    {
        return $query->where('from', $from);
    }

    /**
     * Scope a query to get the last message by wamid.
     */
    public static function scopeFindByWamid(QueryBuilder $query, string $wamid): ?object
    {
        return $query->where('wamid', $wamid)->first();
    }

    /**
     * Scope a query to get the last message.
     */
    public static function scopeLastMessage(QueryBuilder $query): ?object
    {
        return $query->orderByDesc('timestamp')->first();
    }

    /**
     * Scope a query to get the last text message.
     */
    public static function scopeLastTextMessage(QueryBuilder $query): ?object
    {
        return $query->where('type', MessageType::TEXT->value)
            ->orderByDesc('timestamp')
            ->first();
    }

    /**
     * Scope a query to get the messages sent.
     */
    public static function scopeMe(QueryBuilder $query): QueryBuilder
    {
        return $query->where('wamid', 'LIKE', '%==');
    }

    /**
     * Scope a query to get the messages received.
     */
    public static function scopeYou(QueryBuilder $query): QueryBuilder
    {
        return $query->where('wamid', 'NOT LIKE', '%==');
    }

    /**
     * Discriminator aliases for the typed children.
     *
     * Aliases equal the `MessageType` values and must fit the `type` column
     * (`string(20)`). `ContactMessage` owns both `contact` and `contacts`, and
     * `TemplateMessage` owns `template`.
     *
     * @return array<string, class-string<Message>>
     */
    public function childTypes(): array
    {
        return [
            'text' => TextMessage::class,
            'image' => ImageMessage::class,
            'video' => VideoMessage::class,
            'audio' => AudioMessage::class,
            'document' => DocumentMessage::class,
            'sticker' => StickerMessage::class,
            'contact' => ContactMessage::class,
            'contacts' => ContactMessage::class,
            'location' => LocationMessage::class,
            'interactive' => InteractiveMessage::class,
            'template' => TemplateMessage::class,
        ];
    }

    /**
     * Resolve a discriminator alias to a typed child, falling back to the base
     * model for unknown or legacy values so hydration never faults.
     */
    public function classFromAlias(mixed $aliasOrClass): string
    {
        if ($aliasOrClass instanceof BackedEnum) {
            $aliasOrClass = $aliasOrClass->value;
        }

        $alias = is_string($aliasOrClass) ? $aliasOrClass : '';
        $class = $this->getChildTypes()[$alias] ?? null;

        return is_string($class) ? $class : self::class;
    }

    /**
     * Delete the message with its media.
     */
    public function deleteWithMedia(): void
    {
        $this->getMedia()->each(fn ($media) => $media->delete());
        $this->delete();
    }

    /**
     * Check if the message is a message from server.
     */
    public function isMine(): bool
    {
        return str_ends_with($this->wamid, '==');
    }

    /**
     * Check if the message is a message from the client.
     */
    public function isYour(): bool
    {
        return ! $this->isMine();
    }

    /**
     * Get the last message of the chat.
     */
    public function lastInteractive(QueryBuilder $query): ?object
    {
        return $query->where('type', MessageType::INTERACTIVE->value)
            ->orderBy('timestamp', 'desc')
            ->first();
    }

    /**
     * Scope a query to get the last interactive message.
     */
    public function scopeChat(QueryBuilder $query, string $from): QueryBuilder
    {
        return $query->where('from', $from)
            ->orderByDesc('timestamp');
    }

    public function transferMedia(Model&HasMedia $model, string $collection = 'default', bool $deleteOriginal = false): void
    {
        $this->getMedia()->each(fn ($media) => $media->copy($model, $collection));
        if ($deleteOriginal) {
            $this->getMedia()->each(fn ($media) => $media->delete());
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * Overrides Eloquent's protected hook so PHPStan can see the typed return
     * declared in the parent without an untyped property redeclaration in this
     * class.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'message' => 'object',
            'type' => CastsMessageType::class,
            'state' => MessageState::class,
        ];
    }
}
