<?php

declare(strict_types = 1);

namespace Centrex\Wallet;

use Centrex\Wallet\Contracts\WalletTransaction;
use Centrex\Wallet\Enums\WalletType;
use Centrex\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class Wallet
{
    public function for(Model $owner, WalletType|int|null $type = null): WalletModel
    {
        $walletType = $this->resolveWalletType($type);

        return $owner->wallets()->firstOrCreate(
            ['wallet_type_id' => $walletType],
            [
                'name'          => config('wallet.default_name', 'default'),
                'balance'       => 0,
                'currency_code' => config('wallet.default_currency', 'BDT'),
            ],
        );
    }

    public function deposit(WalletModel $wallet, WalletTransaction|int|float|string $amount): WalletModel
    {
        return $wallet->incrementBalance($amount);
    }

    public function withdraw(WalletModel $wallet, WalletTransaction|int|float|string $amount): WalletModel
    {
        return $wallet->decrementBalance($amount);
    }

    public function transfer(WalletModel $from, WalletModel $to, WalletTransaction|int|float|string $amount): void
    {
        DB::transaction(function () use ($from, $to, $amount): void {
            $from->decrementBalance($amount);
            $to->incrementBalance($amount);
        });
    }

    private function resolveWalletType(WalletType|int|null $type): int
    {
        if ($type instanceof WalletType) {
            return $type->value;
        }

        if (is_int($type)) {
            return $type;
        }

        $default = config('wallet.default_wallet_type', WalletType::DEFAULT->value);

        if (!is_int($default)) {
            throw new InvalidArgumentException('wallet.default_wallet_type must be an integer.');
        }

        return $default;
    }
}
