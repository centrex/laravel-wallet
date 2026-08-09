# Known Issues — laravel-wallet

_Last checked: 2026-08-02_

## Failing tests

No failing tests. `vendor/bin/pest -p` reports **3 passed (11 assertions)**. This is a very small test suite for a package that models balances/ledgers — coverage looks thin (only 3 tests total), though nothing observed is actually broken.

## Style / static-analysis debt

- `vendor/bin/pint --test` — **clean, no files flagged.**
- `vendor/bin/rector --dry-run` — **3 files** would be changed, all the same single rule (`AddOverrideAttributeToOverriddenMethodsRector` — missing `#[\Override]` attributes): `src/Wallet.php`, `src/WalletServiceProvider.php:49` (`register()`), `tests/TestCase.php:11` (`setUp()`, `getPackageProviders()`, `getEnvironmentSetUp()`).
- `vendor/bin/phpstan analyse` (level `max`) — **30 errors**, not baselined (`phpstan-baseline.neon` exists but is empty/0 lines). Notable ones:
  - `src/Models/Wallet.php` — access to undefined property `Wallet::$balance` (multiple lines, e.g. 62, 83, 88); `HasOneOrMany::create()` called with an array whose keys (`amount`, `date`, `running_balance`, `transaction_id`, `transaction_type`) aren't recognized as properties of `WalletLedger` (line 107).
  - `src/Wallet.php:20` — call to undefined method `wallets()` on `Illuminate\Database\Eloquent\Model`.
  - `src/Traits/HasUuid.php` — calls to undefined method `getUuidColumnName()` (lines 18, 22, 25); untyped `$uuid_column` property (line 13); generic type params missing on `scopeUuid()` (line 46).
  - `src/Traits/Trashed.php` — calls to undefined methods `withTrashed()` / `onlyTrashed()` on `Illuminate\Database\Eloquent\Builder` (lines 18, 22); deprecated `Request::get()` usage (lines 17, 21); generic type params missing on `scopeTrashed()` (line 15).
  - `src/Models/WalletLedger.php:29,34` — generic relation return types (`BelongsTo`, `MorphTo`) missing type params.

  The undefined-property/undefined-method errors (Wallet::$balance, wallets(), getUuidColumnName(), withTrashed()/onlyTrashed()) suggest PHPStan/Larastan isn't resolving trait-provided members correctly, or these members genuinely don't exist on the classes as currently written — worth a closer look since these aren't just style nits.

## TODO / FIXME markers

None found (`grep -rn "TODO\|FIXME" --include="*.php" src/ config/ database/ tests/`).

## Open GitHub issues

Not checked — the `gh` CLI is not installed in this environment.
