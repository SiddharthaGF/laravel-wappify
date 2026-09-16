# Wappify

## Description

This package helps to receive WhatsApp Cloud API messages in Laravel projects.

## Installation

To install this package, simply run:

```bash
composer require ailuracode/wappify
```

After installing the package, Laravel will auto-load it. You also need to add the service provider in your `config/app.php` file:

```php
'providers' => [
     // Another suppliers...
     AiluraCode\Wappify\WappifyServiceProvider::class,
],
```

## Use

To start using this package in your Laravel project, you only need to run the work queue using the following command:

```bash
php artisan wappify:queue
```

## Security

### Webhook verification

`POST webhook/{account}` deliveries are verified with HMAC-SHA256 (`X-Hub-Signature-256` over the raw
request body, compared with `hash_equals`) against the per-account `app_secret`. Requests with a missing
or invalid signature are rejected with `401` and never dispatched. The `GET` handshake keeps using the
independent per-account `verify_token` and requires no signature.

```php
// config/wappify.php
'accounts' => [
    'default' => [
        'app_secret' => env('WHATSAPP_APP_SECRET'), // Meta App Secret, HMAC key
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'), // handshake token, independent from the API token
    ],
],
```

Provision `WHATSAPP_APP_SECRET` and `WHATSAPP_VERIFY_TOKEN` for every account before deploying: HMAC is
hard-enforced, so unprovisioned hosts answer `401` on every webhook delivery until they provision. Unknown
accounts fail closed (`404`, nothing dispatched).

### Resource access

Resource routes (`messages`, `chat`) require authentication by default (`middleware_resources` is `['auth']`;
anonymous requests get `401`). Deleting a message additionally requires the `delete-whatsapp` gate, which
denies by default: host applications must define ownership, e.g.
`Gate::define('delete-whatsapp', fn ($user, $message) => $message->owner_id === $user->id)`.
Chat reads are paginated (25 per page, same shape as the messages index).

### Queue reliability

`ReceiveMessageJob` is unique per `wamid`, so it requires a lock-capable cache driver (`redis` or `database`;
the `sync` and `array` drivers cannot dedupe, and the `database` driver needs the `cache_locks` table).
Per-job tries, timeout, and backoff come from `accounts.*.queue` (`tries`, `timeout`, `backoff`) and are
passed to `queue:listen` by `wappify:queue`. Failed jobs log the error and rethrow so retries apply.

### Upgrade notes (breaking)

- Webhook ingress now hard-enforces HMAC: provision per-account `app_secret` values (see above).
- Resource routes require authentication by default and chat reads are paginated: bump the minor version and
  update API consumers accordingly.
- Document links are sent verbatim: the hardcoded `.test` to ngrok host rewrite was removed, so sent
  document URLs now match the configured public URL exactly.

## Command actions

Each use case is one invokable command under `AiluraCode\Wappify\Actions`, callable from HTTP,
the queue, and tests. Jobs are thin queue wrappers (queue config, tries, timeout, backoff,
uniqueness) that delegate to commands via the container.

| Command | Does |
|---|---|
| `VerifyWebhookChallenge` | Returns the GET challenge for a valid token (`403` bad token, `404` unknown account) |
| `EnqueueInboundPayload` | Dispatches `ReceiveMessageJob` with the raw body and acknowledges; never parses |
| `IngestInboundMessage` | Persists an inbound message once by `wamid` (redelivery-safe), then mark-read + auto-download |
| `ApplyStatusTransition` | Advances an existing row's lifecycle state; unknown/disallowed transitions are ignored, nothing is inserted |
| `SendTextMessage` | One transport call + exactly one row |
| `SendDocumentMessage` | One document send (verbatim `Media::getUrl`, `Document: {name}` caption) + exactly one row |
| `SendButtonReplyMessage` | One button send + exactly one row; logs once, then rethrows for retry |
| `DownloadMessageMedia` | Attaches media as `{wamid-stem}.{ext}` (image/audio/video/pdf only); `failed()` cleans partial files |
| `DeleteMessage` | Deletes the row, or media-then-row in a transaction; missing rows throw `ModelNotFoundException` |

`AiluraCode\Wappify\Support\PayloadMapper` is the pure, framework-free mapping layer
(raw JSON / transport responses to `IncomingMessageData` / `StatusUpdatePayload`).

Single persistence owner rule: commands own the one `save()` per outbound send. The
`WhatsAppCloudApi` subclass is pure transport and MUST NOT self-persist responses.

```php
use AiluraCode\Wappify\Actions\SendTextMessage;

$message = (new SendTextMessage('593960800736', 'hello'))();
```

## Removed legacy surface

The legacy `Wappify` statics, the `whatsapp()` / `webhook()` helpers, and the `to*()` / `is*()` transformation shims were removed. Use the commands in `AiluraCode\Wappify\Actions` (and `PayloadMapper`) and the typed message models under `AiluraCode\Wappify\Models\Messages` with the `state` lifecycle column. See [CHANGELOG.md](CHANGELOG.md) for details.

## License

This package is released under the MIT License. For more details, please refer to the [LICENSE](LICENSE) file.

## Credits

- Developed by [SiddharthaGF](https://github.com/SiddharthaGF) for AiluraCode.
- Use the library [netflie/whatsapp-cloud-api](https://github.com/netflie/whatsapp-cloud-api).
- Use GuzzleHttp to make HTTP requests.
- Use [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary) for multimedia file management.
- Use PHPUnit for unit testing.

## Project status

This package is under active development and is continually being improved. It is recommended to stay tuned for future updates for new features and improvements.