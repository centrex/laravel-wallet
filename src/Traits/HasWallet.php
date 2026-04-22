<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Traits;

use Centrex\Wallet\Enums\WalletType;
use Centrex\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallet
{
    public static function bootHasWallet(): void
    {
        static::deleting(function (Model $model): void {
            $model->wallets()->delete();
        });

        static::created(function (Model $model): void {
            $model->wallets()->firstOrCreate(
                ['wallet_type_id' => config('wallet.default_wallet_type', WalletType::DEFAULT->value)],
                [
                    'name'          => config('wallet.default_name', 'default'),
                    'currency_code' => config('wallet.default_currency', 'BDT'),
                    'balance'       => 0,
                ],
            );
        });
    }

    public function wallets(): MorphMany
    {
        return $this->morphMany(Wallet::class, 'user');
    }

    public function wallet(WalletType|int|null $walletType = null): ?Wallet
    {
        $walletType ??= config('wallet.default_wallet_type', WalletType::DEFAULT->value);
        $walletType = $walletType instanceof WalletType ? $walletType->value : $walletType;

        return $this->wallets()->where('wallet_type_id', $walletType)->first();
    }
}
