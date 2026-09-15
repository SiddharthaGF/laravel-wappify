# Tasks: security-hardening

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~700-850 total (source ~300, tests ~450) |
| 400-line budget risk | High |
| Chained PRs recommended | No |
| Suggested split | single PR |
| Delivery strategy | exception-ok (maintainer-approved `size:exception`, 2026-09-15) |
| Chain strategy | size-exception |

Decision needed before apply: No (all resolved 2026-09-15)
Chained PRs recommended: No
Chain strategy: size-exception
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| 1 | HMAC ingress + fail-closed accounts | PR 1 (single) | `./vendor/bin/phpunit --filter WebhookHmacTest` | N/A (HTTP-level, covered by feature tests) | Middleware + config + helper revertable together |
| 2 | Job failure contract + idempotent ingest | PR 1 (single) | `./vendor/bin/phpunit --filter IdempotentIngestTest` | N/A (queue path covered by feature tests) | Jobs revertable without touching ingress |
| 3 | Auth-by-default + pagination + destroy authZ | PR 1 (single) | `./vendor/bin/phpunit --filter ResourceAuthTest` | N/A | Config default + controllers revertable together |
| 4 | Verbatim document URLs | PR 1 (single) | `./vendor/bin/phpunit --filter DocumentUrlTest` | N/A | One-line deletion, trivially revertable |
| 5 | Regression test plan | PR 1 (single) | `./vendor/bin/phpunit` (full suite) | Full PHPUnit suite | Tests-only |
| 6 | Docs + rollout notes | PR 1 (single) | N/A (docs) | N/A | Docs-only |

## Unit 1 — A: HMAC ingress + fail-closed accounts

- [x] 1.1 Modify `src/Http/Middleware/FacebookMiddleware.php`: HMAC-SHA256 over `$request->getContent()` vs `X-Hub-Signature-256`, `hash_equals`, POST-only, drop UA check; GET passes through.
- [x] 1.2 Modify `src/Data/WhatsappAccountConfig.php` + `config/wappify.php`: add `app_secret` (env `WHATSAPP_APP_SECRET`) + `verify_token`; `fromConfig` throws on unknown (remove empty-config fallback). Delete `src/Data/FacebookHeaderConfig.php`.
- [x] 1.3 Modify `src/Http/Controllers/WebhookController.php` + `src/helpers.php`: `receive(Request)` raw-body dispatch with 404 unknown; `webhook(Request)` returns challenge string, no `echo`/`$_GET`.
- [x] 1.4 Test: `tests/Feature/WebhookHmacTest.php::{accepts_valid, rejects_forged, rejects_missing, preserves_get_challenge}` + `tests/Feature/UnknownAccountTest.php::unknown_account_never_dispatches`. Done: forged → 401 zero dispatches; GET handshake intact.

## Unit 2 — B: Job failure contract + idempotent ingest

- [x] 2.1 Modify `src/Jobs/ReceiveMessageJob.php`: remove `dd()` → `Log::error` + rethrow; `ShouldBeUnique` + `uniqueId()` = wamid; `firstOrCreate` on UNIQUE wamid + catch `QueryException` 23000 as success; tries 3 / timeout 5 / backoff [1, 5, 15] from `WhatsappQueueConfig`.
- [x] 2.2 Modify `src/Jobs/SendButtonReplyMessageJob.php`: remove `dd()` → `Log::error` + rethrow; tries/backoff/timeout. Modify `src/Jobs/DownloadMediaJob.php`: constructor stores ids only; `toMedia`/mime/extension move to `handle()`; allowlist image/audio/video/*+pdf else fail; `failed()` cleans partial media.
- [x] 2.3 Modify `src/Console/Commands/RunWhatsappQueue.php`: add `--backoff` from config alongside existing `--tries`/`--timeout`.
- [x] 2.4 Test: `tests/Feature/JobFailureSemanticsTest.php::no_dd_and_bubbles`, `IdempotentIngestTest.php::{redelivery_single_row, race_no_exception}`, `QueueConfigTest.php::flags_match_config`, `DownloadMediaCtorTest.php::{ctor_never_throws, bad_mime_rejected}`. Done: no `dd()`/`echo` in `src/`; duplicate wamid → single row.

## Unit 3 — D: Auth-by-default + paginated reads + destroy authZ

- [x] 3.1 Modify `config/wappify.php`: `middleware_resources` default `['auth']`. Modify `src/Http/Controllers/MessagesController.php::destroy`: Gate `delete-whatsapp` authorize (non-owner → 403) + `DB::transaction(fn () => deleteWithMedia())` media-first.
- [x] 3.2 Modify `src/Http/Controllers/ChatController.php`: `chat`/`me`/`you` return `paginate(25)` identical shape to `MessagesController::index`. Reorder `src/Models/Whatsapp.php::deleteWithMedia()` media-before-row if needed.
- [x] 3.3 Test: `tests/Feature/ResourceAuthTest.php::{anon_401, stranger_403, owner_200}`, `ChatPaginationTest.php::pages_bounded_with_meta`, `DestroyMediaTest.php::atomic_media_first`. Done: anon → 401, stranger → 403, 60 rows → 25 + meta, media failure retains row.

## Unit 4 — C: Verbatim document URLs

- [x] 4.1 Modify `src/Jobs/SendDocumentMessageJob.php`: delete `.test` → ngrok `str_replace`; use `$document->getUrl()` verbatim.
- [x] 4.2 Test: `tests/Feature/DocumentUrlTest.php::no_ngrok_rewrite`. Done: sent URL unmodified.

## Unit 5 — Test plan

- [x] 5.1 Add all `tests/Feature/` files above (10 files, ~450 lines); run `./vendor/bin/phpunit --filter=` per unit, then full suite green.
- [x] 5.2 Run `vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress` and `--configuration=phpstan-tests.neon`: zero errors, zero new ignores. Run `vendor/bin/pint --test`: clean.

## Unit 6 — Docs + rollout notes

- [x] 6.1 Document `accounts.*.app_secret` / `verify_token` schema, lock-driver requirement (`ShouldBeUnique` is a no-op on the sync driver), and rollout notes: D1 hard-enforce + migration docs, D2 breaking minor bump + changelog, D3 breaking announcement.
