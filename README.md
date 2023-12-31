# Add wallet functionality in laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/centrex/laravel-wallet.svg?style=flat-square)](https://packagist.org/packages/centrex/laravel-wallet)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-wallet/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/centrex/laravel-wallet/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/centrex/laravel-wallet/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/centrex/laravel-wallet/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/centrex/laravel-wallet?style=flat-square)](https://packagist.org/packages/centrex/laravel-wallet)

Easily add a virtual wallet to your Laravel models. Features multiple wallets and a ledger system to help keep track of all transactions in the wallets.

## Contents

- [Installation](#installation)
- [Usage Examples](#usage)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require centrex/laravel-wallet
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-wallet-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-wallet-views"
```

## Usage

First, you'll need to add the `HasWallets` trait to your model.

```php
use Centrex\Wallet\Traits\HasWallets;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, HasWallets;
}
```

By adding the `HasWallets` trait, you've essentially added all the wallet relationships to the model.

You can start by creating a wallet for the given model.

```php
$user = User::find(1);

$wallet = $user->wallets()->create();
```

You can then increment the wallet balance by:

```php
$wallet->incrementBalance(100);
```

Or decrement the balance by:

```php
$wallet->decrementBalance(100);
```

To get the balance of the wallet, you can use the `balance` accessor:

```php
$wallet->balance;
```

A wallet can be accessed using the `wallet()` method in the model:

```php
$user->wallet();
```

You can set up multiple types of wallets by defining a `WalletType`. Simply create a wallet type entry in the 
`wallet_types` table and create a wallet using this wallet type.

```php
use CoreProc\WalletPlus\Models\WalletType;

$walletType = WalletType::create([
    'name' => 'Peso Wallet',
    'decimals' => 2, // Set how many decimal points your wallet accepts here. Defaults to 0.
]);

$user->wallets()->create(['wallet_type_id' => $walletType->id]);
```

You can access a model's particular wallet type by using the `wallet()` method as well:

```php
$pesoWallet = $user->wallet('Peso Wallet'); // This method also accepts the ID of the wallet type as a parameter

$pesoWallet->incrementBalance(100);

$pesoWallet->balance; // Returns the updated balance without having to refresh the model.
```

All movements made in the wallet are recorded in the `wallet_ledgers` table.

### Defining Transactions

Ideally, we want to record all transactions concerning the wallet by linking it to a transaction model. Let's say we
have a `PurchaseTransaction` model which holds the data of a purchase the user makes in our app.

```php
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    //
}
```

We can link this `PurchaseTransaction` to the wallet ledger by implementing the `WalletTransaction` contract to this 
model and using this transaction to decrement (or increment, whatever the case may be) the wallet balance.

```php
use CoreProc\WalletPlus\Contracts\WalletTransaction;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model implements WalletTransaction
{
    public function getAmount() 
    {
        return $this->amount;
    }
}
```

Now we can use this in the wallet:

```php
$wallet = $user->wallet('Peso Wallet');

$purchaseTransaction = PurchaseTransaction::create([
    ...,
    'amount' => 100,
]);

$wallet->decrementBalance($purchaseTransaction);
```

By doing this, you will be able to see in the `wallet_ledgers` table the transaction that is related to the movement
in the wallet.

## Testing

🧹 Keep a modern codebase with **Pint**:
```bash
composer lint
```

✅ Run refactors using **Rector**
```bash
composer refacto
```

⚗️ Run static analysis using **PHPStan**:
```bash
composer test:types
```

✅ Run unit tests using **PEST**
```bash
composer test:unit
```

🚀 Run the entire test suite:
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [centrex](https://github.com/centrex)
- [All Contributors](../../contributors)
- [CoreProc/laravel-wallet-plus](https://github.com/CoreProc/laravel-wallet-plus)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
