# Tasks: Command Actions Migration

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~900–1200 (10 new files + 9 modified + 2 new tests + docs) |
| 400-line budget risk | High |
| Chained PRs recommended | No (single-pr strategy; exception path instead) |
| Suggested split | Single PR of 6 work-unit commits (Units 1→6 in order) |
| Delivery strategy | exception-ok (maintainer-approved `size:exception`, 2026-09-15) |
| Chain strategy | size-exception |

Decision needed before apply: No (all resolved 2026-09-15: hand-rolled invokables, size:exception)
Chained PRs recommended: No
Chain strategy: size-exception
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Likely PR | Focused test command | Runtime harness | Rollback boundary |
|------|------|-----------|----------------------|-----------------|-------------------|
| 1 | PayloadMapper + RED persist tests | PR 1 (single) | `./vendor/bin/phpunit tests/Feature/SinglePersistTest.php tests/Feature/WappifyTest.php` | N/A (pure mapping + RED proof) | Delete 2 new files only |
| 2 | Inbound commands + ReceiveMessageJob | PR 1 (single) | `./vendor/bin/phpunit tests/Feature/IdempotentIngestTest.php tests/Feature/StatusWebhookTest.php tests/Feature/UnknownAccountTest.php` | POST /webhook e2e (receive→dispatch→ack) | Revert Unit 2 files; mapper untouched |
| 3 | Send commands + single persist owner | PR 1 (single) | `./vendor/bin/phpunit tests/Feature/SinglePersistTest.php tests/Feature/DocumentUrlTest.php tests/Feature/JobFailureSemanticsTest.php tests/Feature/QueueConfigTest.php` | Sync `new SendTextMessage(to,text)(fakeTransport)` | Revert Unit 3 files; inbound slice keeps working |
| 4 | Ops commands + remaining wiring | PR 1 (single) | Focused files below | GET /webhook challenge + DELETE e2e | Revert Unit 4 files only |
| 5 | Deprecated facade | PR 1 (single) | `./vendor/bin/phpunit tests/Feature/MessageShimTest.php tests/Feature/WappifyTest.php` | N/A | Revert 2 files; commands unaffected |
| 6 | Full gate + docs | PR 1 (single) | `./vendor/bin/phpunit` (full suite) | Full suite is the harness | Docs-only revert |

## Unit 1: PayloadMapper + RED persist tests (foundation; no behavior change)

- [x] 1.1 Create `src/Support/PayloadMapper.php` (static `fromJson/fromDecoded/fromResponse/statusFromJson/toModel`; move `Wappify` privates verbatim; InvalidArgumentException on bad JSON).
- [x] 1.2 Add `tests/Feature/SinglePersistTest.php` with RED `test_one_text_send_creates_one_row` + `test_sdk_send_writes_no_row` (must FAIL: double-persist still present).
- [x] 1.3 Test: `vendor/bin/phpunit tests/Feature/SinglePersistTest.php tests/Feature/WappifyTest.php`. Rollback: delete 2 new files only.

## Unit 2: Inbound commands + ReceiveMessageJob

- [x] 2.1 Create `src/Actions/IngestInboundMessage.php` (firstOrCreate by wamid + verbatim 23000-resolve; mark-read/auto-download chain), `src/Actions/ApplyStatusTransition.php` (silent-ignore unknown/disallowed/terminal-Read; zero inserts), `src/Actions/EnqueueInboundPayload.php` (dispatch only, never parse).
- [x] 2.2 Thin `src/Jobs/ReceiveMessageJob.php` (keep ctor+ShouldBeUnique+uniqueId; `handle()` delegates via `app()`; log-and-bubble) + wire `src/Http/Controllers/WebhookController.php` to commands.
- [x] 2.3 Test: `IdempotentIngestTest`, `StatusWebhookTest`, `UnknownAccountTest::test_unknown_account_never_dispatches`. Rollback: revert Unit 2 files; mapper untouched.

## Unit 3: Send commands + single persist owner (GREEN the RED)

- [ ] 3.1 Create `src/Actions/SendTextMessage.php`, `src/Actions/SendDocumentMessage.php` (verbatim `Media::getUrl`, `Document: {name}`), `src/Actions/SendButtonReplyMessage.php` (Log::error + rethrow); each ctor inputs + `__invoke(?WhatsAppCloudApi $transport = null)`, owns single `save()` via mapper.
- [ ] 3.2 Strip auto-save overrides in `src/WhatsAppCloudApi.php` (pure transport) + thin `src/Jobs/SendTextMessageJob.php`, `src/Jobs/SendDocumentMessageJob.php`, `src/Jobs/SendButtonReplyMessageJob.php` (keep ctors/tries/timeout/backoff; delegate via `app()`).
- [ ] 3.3 Test: Unit 1 REDs go GREEN; `DocumentUrlTest`, `JobFailureSemanticsTest`, `QueueConfigTest`. Rollback: revert Unit 3 files; inbound slice keeps working.

## Unit 4: Ops commands (verify/media/delete) + remaining wiring

- [ ] 4.1 Create `src/Actions/VerifyWebhookChallenge.php` (404 unknown / 403 bad token), `src/Actions/DownloadMessageMedia.php` (MIME gate, `{wamid-stem}.{ext}`, `failed()` cleanup), `src/Actions/DeleteMessage.php` (404 missing; media-then-row transaction); thin `src/Jobs/DownloadMediaJob.php`; wire `WebhookController` + `MessagesController`.
- [ ] 4.2 Test: `WebhookHmacTest`, `DownloadMediaCtorTest`, `DestroyMediaTest`. Rollback: revert Unit 4 files only.

## Unit 5: Deprecated facade (delegates only, no deletion)

- [ ] 5.1 Modify `src/Wappify.php`, `src/helpers.php` (`whatsapp()`/`webhook()`): keep signatures, delegate to commands/mapper, emit `E_USER_DEPRECATED`, `@deprecated … will be removed in v2.0`.
- [ ] 5.2 Test: `MessageShimTest::test_every_surviving_shim_is_marked_deprecated`, `WappifyTest::test_catch_method`. Rollback: revert 2 files; commands unaffected.

## Unit 6: Full gate + docs

- [ ] 6.1 Run FULL suite (`vendor/bin/phpunit`); stays-put gate: `MessageLifecycleTest`, `MessageTypeCastTest`, `ChatPaginationTest` unmodified-green.
- [ ] 6.2 Docs: command catalog (9 commands + mapper + ownership rule) + facade deprecation + v2.0 removal note.
- [ ] 6.3 Run `vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=512M --no-progress` and `--configuration=phpstan-tests.neon`: zero errors, zero new ignores. Run `vendor/bin/pint --test`: clean.
