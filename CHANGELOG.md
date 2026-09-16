# Changelog

All notable changes to this package are documented in this file.

## [Unreleased]

### Added

- HMAC-SHA256 verification of Meta webhook deliveries (`X-Hub-Signature-256` over the raw body, `hash_equals`) against a per-account `app_secret`. Forged or missing signatures get `401` with zero dispatches; unknown accounts fail closed with `404`.
- Independent per-account `verify_token` for the `GET` webhook handshake.
- `ShouldBeUnique` wamid ingest: redelivered payloads resolve to a single row, and a lost unique-constraint race (SQLSTATE `23000`) is absorbed as a duplicate success.
- Per-job tries, timeout, and backoff sourced from `accounts.*.queue` and passed to `queue:listen` by `wappify:queue` (`--tries`, `--timeout`, `--backoff`).
- `delete-whatsapp` gate (default-deny; host apps define ownership) authorizing `MessagesController::destroy`.
- Typed message models resolved from the `type` discriminator through Parental single-table inheritance.
- `message-lifecycle` state machine on the new `state` column: `waiting` -> `sent` -> `delivered` -> `read` (`read` is terminal).
- Additive migration that adds `state` (default `waiting`) and backfills it from `message->status` in PHP (driver-portable, idempotent, and write-only-when-different).

### Changed

- Resource routes require authentication by default (`middleware_resources` is `['auth']`); anonymous requests get `401`. (Breaking: update API consumers.)
- `ChatController::chat`, `::me`, and `::you` return a `LengthAwarePaginator` (25 per page, identical shape to `MessagesController::index`) instead of unbounded collections. (Breaking: update API consumers.)
- `MessagesController::destroy?withMedia` now runs `deleteWithMedia()` inside a DB transaction with media deleted before the row, atomically.
- `ReceiveMessageJob` and the `SendButtonReplyMessage` action log handler errors and rethrow (retries/backoff apply) instead of dumping with `dd()`. `DownloadMediaJob` resolves media in `handle()`, rejects non-image/audio/video/pdf MIME types, and cleans partial media in `failed()`.
- `DownloadMediaJob` accepts a Whatsapp row id and never throws from its constructor.
- Document links are sent verbatim: the hardcoded `.test` to ngrok host rewrite was removed. (Breaking: sent URLs now match the configured public URL exactly.)
- Status webhooks now apply a state transition to an existing row and never persist a new record.
- The `type` column is cast through a tolerant cast that returns the `MessageType` enum for known aliases and the raw string otherwise; unknown or legacy values fall back to the base model.
- Controllers now declare their routes with traditional/explicit route definitions in `routes/api.php` instead of registration-time attributes.
- Typed accessors on the row-level `WhatsApp` model (`getWamId`, `setWamId`, `getProfile`, `setProfile`, `getFrom`, `setFrom`, `getTimestamp`, `setTimestamp`, `getType`, `setType`, `getMessage`, `setMessage`) now declare PHP-native return types and parameter types instead of relying on `@property` docblocks and `@var` annotations. The `getMessage()` accessor returns `\stdClass` (the exact runtime type of the Eloquent `object` cast) instead of the broader `object`.
- Internal row-data shapes (`responseToModel`, `payloadToModel`, `payloadToStatus`, `createFromModel`) now carry native PHP array-shape signatures in PHPDoc, replacing the previous loose `array` return type and per-call `@var` hints. Internal intermediates use typed locals so the PHP runtime and PHPStan both enforce the shape.
- The webhook `account.queue` configuration is read through a typed local that narrows `Config::get(...)` from `mixed` to the documented `array{connection: string, name: string, tries: int, timeout: int}` shape, instead of relying on `@phpstan-import-type` aliases.

### Deprecated

- The legacy `to*()` and `is*()` transformation members are deprecated compatibility shims. They keep working for one release cycle and will be removed in the NEXT MAJOR version.

### Removed

- The spoofable `User-Agent` webhook allowlist (`FacebookHeaderConfig` and the `wappify.middleware.facebook.headers` keys); Meta deliveries are verified by HMAC instead. (Breaking: provision per-account `app_secret` values.)
- `MessageType::STATUS` and the `toStatus()`, `isStatus()`, `getStatus()`, `hasStatus()`, and `isDownloadable()` members.
- The `wappify.download.allowed` configuration entry and the unused `CAST_TO_IMAGE_EXCEPTION` entries.
- The attribute-based routing surface: `AiluraCode\Wappify\Attributes\*` (`#[AiluraController]`, `#[AiluraRoute]`), the reflection-based `add_route()` helper, and the `wappify.api.resources` / `wappify.api.webhooks` configuration keys.
- The `ShouldMessage` row-level accessor methods (`getWamId`, `setWamId`, `getProfile`, `setProfile`, `getFrom`, `setFrom`, `getTimestamp`, `setTimestamp`, `getType`, `setType`, `getMessage`, `setMessage`, `validateProperty`) are no longer callable on DTOs under `AiluraCode\Wappify\Entities\*`. The DTO hierarchy never implemented this interface, but the removal of the methods from `BaseMessage` and the inheritance chain codifies the split: row-level concerns live exclusively on the Eloquent `WhatsApp` model, while the DTOs only expose typed payload accessors (`getBody`, `getId`, `getMimeType`, `getInteractiveType`, etc.). Consumers must call `$whatsapp->getWamId()` against the model instance, not against a DTO.