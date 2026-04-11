# CLAUDE.md

## Package Overview

`centrex/laravel-wallet` — Digital wallet with transaction support for Laravel applications.

Namespace: `Centrex\Wallet\`  
Service Provider: `WalletServiceProvider`  
Facade: `Facades/Wallet`  
Main class: `Wallet`

## Commands

Run from inside this directory (`cd laravel-wallet`):

```sh
composer install          # install dependencies
composer test             # full suite: rector dry-run, pint check, phpstan, pest
composer test:unit        # pest tests only
composer test:lint        # pint style check (read-only)
composer test:types       # phpstan static analysis
composer test:refacto     # rector refactor check (read-only)
composer lint             # apply pint formatting
composer refacto          # apply rector refactors
composer analyse          # phpstan (alias)
composer build            # prepare testbench workbench
composer start            # build + serve testbench dev server
```

Run a single test:
```sh
vendor/bin/pest tests/ExampleTest.php
vendor/bin/pest --filter "test name"
```

## Structure

```
src/
  Wallet.php
  WalletServiceProvider.php
  Facades/
  Commands/
  Contracts/
  Enums/                        # Transaction type enums (credit, debit, etc.)
  Models/                       # Wallet and Transaction models
  Traits/                       # HasWallet trait
config/config.php
database/migrations/
tests/
workbench/
```

## Key Concepts

- `Traits/HasWallet`: add to any model (User, etc.) to give it a wallet
- `Models/Wallet`: stores balance per owner
- `Models/Transaction`: immutable ledger entries (debit/credit)
- Enums define transaction types
- Contracts define wallet interface for type safety

## Usage

```php
use Centrex\Wallet\Traits\HasWallet;

class User extends Model
{
    use HasWallet;
}

$user->wallet->deposit(1000);
$user->wallet->withdraw(500);
$balance = $user->wallet->balance;
```

## Conventions

- PHP 8.2+, `declare(strict_types=1)` in all files
- Pest for tests, snake_case test names
- Pint with `laravel` preset
- Rector targeting PHP 8.3 with `CODE_QUALITY`, `DEAD_CODE`, `EARLY_RETURN`, `TYPE_DECLARATION`, `PRIVATIZATION` sets
- PHPStan at level `max` with Larastan
