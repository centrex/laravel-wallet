<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Traits;

use Centrex\Wallet\Models\Wallet;
use Illuminate\Database\Eloquent\Model;

trait HasWallet
{
    public static function bootHasWallet(): void
    {
        static::deleting(function (Model $model): void {
            $model->wallets()->delete();
        });

        static::created(function (Model $model): void {
            Wallet::create(['user_id' => $model->user_id, 'user_type' => $model::class]);
        });
    }

    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'user');
    }

    public function wallet($walletType = null)
    {
        if ($walletType) {
            return $this->wallets()->where('wallet_type_id', $walletType)->first();
        }
    }
}
