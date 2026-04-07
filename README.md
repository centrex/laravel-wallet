# Add wallet functionality to Laravel applications

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/laravel-wallet.svg?style=flat-square)](https://packagist.org/packages/centrex/laravel-wallet)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-wallet/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/laravel-wallet/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-wallet/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/laravel-wallet/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/laravel-wallet?style=flat-square)](https://packagist.org/packages/centrex/laravel-wallet)

Polymorphic digital wallet for any Eloquent model. Supports multiple wallet types per user, balance increments/decrements, and an immutable ledger of all transactions.

## Installation

```bash
composer require centrex/laravel-wallet
php artisan vendor:publish --tag="laravel-wallet-migrations"
php artisan migrate
```

## Usage

### 1. Add the trait to your model

```php
use Centrex\Wallet\Traits\HasWallet;

class User extends Authenticatable
{
    use HasWallet;
}
```

A default wallet is created automatically when the model is created.

### 2. Access wallets

```php
// All wallets for this user
$user->wallets;

// Specific wallet by type ID
$wallet = $user->wallet(WalletType::DEFAULT->value);

echo $wallet->balance;
```

### 3. Credit and debit

```php
// Credit by amount
$wallet->incrementBalance(500.00);

// Debit by amount
$wallet->decrementBalance(200.00);

// Credit via a WalletTransaction model (links transaction to ledger entry)
$wallet->incrementBalance($transaction);
$wallet->decrementBalance($transaction);
```

### 4. Ledger

Every balance change is recorded in `wallet_ledgers` with the transaction reference and running balance:

```php
$wallet->walletLedgers;
// → collection of WalletLedger with: amount, running_raw_balance, transaction_id, transaction_type
```

### Wallet types

```php
use Centrex\Wallet\Enums\WalletType;

WalletType::DEFAULT   // value: 1
WalletType::TEMPORAY  // value: 2
```

### Cleanup on delete

When the parent model is deleted, all its wallets are automatically deleted via the `deleting` boot hook.

## Testing

```bash
composer test        # full suite
composer test:unit   # pest only
composer test:types  # phpstan
composer lint        # pint
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [centrex](https://github.com/centrex)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
