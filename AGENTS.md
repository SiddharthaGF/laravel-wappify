# AGENTS.md

Laravel **package** (`ailuracode/wappify`), not an app: no `.env`, no `artisan serve`, no host app. Tests run under Orchestra Testbench with sqlite `:memory:`. Namespace `AiluraCode\Wappify\` → `src/` (PSR-4), tests → `AiluraCode\Wappify\Tests\`. Default branch is `master`; CI runs on push + PRs. **Dev/test floor is PHP 8.2 / Laravel 12 / orchestra/testbench 10** — `laravel/framework` is pinned to `^12.61.1` because every earlier release is covered by the CRLF-injection and signed-URL advisories.

## Commands (verified on Linux)

- **`vendor/bin/phpunit` is the canonical test command** (`composer test` now runs `phpunit`, but the bare direct path is unambiguous).
- Style: `vendor/bin/pint --test` (or `composer pint`); auto-fix with `composer pint:fix`. Pint is the ONLY formatter installed — the `phpcs`/`php-cs-fixer` commands in `openspec/config.yaml` are stale and do not exist in `vendor/bin`.
- Static analysis: `composer phpstan` runs both configs (max level): `phpstan.neon` over `src/`, `config/`, `database/`, `routes/`, plus `phpstan-tests.neon` over `tests/`.
- `vendor/bin/rector process --dry-run` for refactor preview. `rector.php` deliberately skips `LocallyCalledStaticMethodToNonStaticRector` (helpers stay static) and the `ThrowIfRector`s (explicit throws) — do not reintroduce those changes.
- No xdebug/pcov installed → no coverage output available.
- `composer audit` is clean: `laravel/framework` is pinned to a patched line, so a local `composer update` needs no advisory workaround. Do not re-add `policy.advisories.block false` — if an update is blocked by an advisory, fix the constraint instead.

## Architecture rules (easy to violate)

- **Command layer:** every use case is an invokable command in `src/Actions` (e.g. `(new SendTextMessage($to, $text))()`). Controllers, jobs, and tests all call commands; jobs are thin queue wrappers that resolve commands via the container.
- **Single persistence owner:** exactly one `save()` per outbound send, owned by the command. `src/WhatsAppCloudApi.php` is pure transport and MUST NOT self-persist. Never add persistence to jobs, models, or controllers.
- **Mapping:** `src/Support/PayloadMapper.php` is the pure, framework-free layer mapping raw JSON / transport responses to `IncomingMessageData` / `StatusUpdatePayload`.
- **Typed messages:** `src/Models/Whatsapp.php` uses Parental `HasChildren`; children in `src/Models/Messages/` are selected by the `type` column via `childTypes()` (aliases = `MessageType` values). Unknown or legacy aliases hydrate as the base `Whatsapp` — hydration must never fault. All children share the root morph class (see `Messages\Message::getMorphClass`).
- **Lifecycle:** spatie/laravel-model-states — `Waiting → Sent → Delivered → Read` (Read terminal), configured in `src/States/MessageState.php`, default `Waiting`. `ApplyStatusTransition` ignores unknown/disallowed transitions; it never inserts rows.
- **Webhook security (hard-enforced):** `POST webhook/{account}` requires HMAC-SHA256 (`X-Hub-Signature-256`, compared with `hash_equals`) against the per-account `app_secret`; missing/invalid → 401, unknown account → 404. The GET handshake uses the independent per-account `verify_token`.
- **Queue:** `ReceiveMessageJob` is unique per `wamid`, so hosts need a lock-capable cache (`redis`, or `database` with `cache_locks`); `array`/`sync` cannot dedupe.
- **Routes** load under `api/{config('wappify.api.path', 'whatsapp')}` with names `wappify.*`; resource routes default to `auth` middleware; message delete is gated by `delete-whatsapp`, defined to DENY by default (hosts must define ownership, e.g. via `Gate::define`).

## Testing

- `phpunit.xml` declares ONE suite: `tests/Feature` (no Unit dir). Verified green: 49 tests / 176 assertions.
- `tests/TestCase.php` is Testbench with sqlite `:memory:` and the package provider. `phpunit.xml` sets `WHATSAPP_API_TOKEN` / `WHATSAPP_API_PHONE_NUMBER_ID` to empty.
- Fixtures: `stubs/messageText.stub.json` (webhook payload used by tests) and `stubs/HasAttributes.stub` — the stub is loaded ONLY by PHPStan (`phpstan.neon`) and must not be deleted.
- `openspec/config.yaml`'s test baseline (5 tests / 1 known error in `WappifyTest::testCast`) is STALE: the legacy surface and that test were removed; the whole suite passes now.

## SDD / OpenSpec

- Durable changes follow OpenSpec SDD under `openspec/changes/<change>/`; `openspec/config.yaml` declares `strict_tdd: true`.
- `typed-message-models` is implemented (tasks.md + verify-report.md present). `command-actions` and `security-hardening` are planning-only (no tasks yet).