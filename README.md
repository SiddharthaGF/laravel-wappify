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

## Deprecations

The legacy `to*()` and `is*()` message transformation members are deprecated compatibility shims. They keep working for one release cycle and will be removed in the next major version. Prefer the typed message models under `AiluraCode\Wappify\Models\Messages` and the `state` lifecycle column. See [CHANGELOG.md](CHANGELOG.md) for details.

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