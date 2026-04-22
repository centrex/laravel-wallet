<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Models;

use Centrex\Wallet\Contracts\WalletTransaction;
use Centrex\Wallet\Enums\WalletType;
use Centrex\Wallet\Traits\{HasUuid, Trashed};
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{HasMany, MorphTo};
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

final class Wallet extends Model
{
    use HasUuid;
    use SoftDeletes;
    use Trashed;

    protected $fillable = [
        'name',
        'wallet_type_id',
        'balance',
        'user_id',
        'user_type',
        'currency_code',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo('user');
    }

    public function getWalletTypeAttribute(): ?WalletType
    {
        return WalletType::tryFrom((int) $this->wallet_type_id);
    }

    public function walletLedgers(): HasMany
    {
        return $this->hasMany(WalletLedger::class);
    }

    public function incrementBalance(WalletTransaction|int|float|string $transaction): self
    {
        return $this->applyBalanceDelta($transaction, 'increment');
    }

    public function decrementBalance(WalletTransaction|int|float|string $transaction): self
    {
        return $this->applyBalanceDelta($transaction, 'decrement');
    }

    public function hasSufficientBalance(int|float|string $amount): bool
    {
        return (float) $this->balance >= $this->normalizeAmount($amount);
    }

    private function applyBalanceDelta(WalletTransaction|int|float|string $transaction, string $direction): self
    {
        $amount = $this->extractAmount($transaction);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Wallet amounts must be greater than zero.');
        }

        if ($direction === 'decrement'
            && !config('wallet.allow_negative_balances', false)
            && !$this->hasSufficientBalance($amount)) {
            throw new RuntimeException('Insufficient wallet balance.');
        }

        return DB::transaction(function () use ($transaction, $direction, $amount): self {
            /** @var self $wallet */
            $wallet = self::query()->lockForUpdate()->findOrFail($this->getKey());
            $signedAmount = $direction === 'decrement' ? -$amount : $amount;
            $newBalance = $wallet->normalizeAmount(((float) $wallet->balance) + $signedAmount);

            $wallet->forceFill(['balance' => $newBalance])->save();
            $wallet->createWalletLedgerEntry($transaction, $signedAmount, $newBalance);

            $this->forceFill(['balance' => $wallet->balance]);

            return $this;
        });
    }

    private function createWalletLedgerEntry(WalletTransaction|int|float|string $transaction, float $signedAmount, float $runningBalance): WalletLedger
    {
        $payload = [
            'date' => now()->toDateString(),
            'amount' => $this->normalizeAmount($signedAmount),
            'running_balance' => $this->normalizeAmount($runningBalance),
        ];

        if ($transaction instanceof Model) {
            $payload['transaction_id'] = $transaction->getKey();
            $payload['transaction_type'] = $transaction->getMorphClass();
        }

        return $this->walletLedgers()->create($payload);
    }

    private function extractAmount(WalletTransaction|int|float|string $transaction): float
    {
        $amount = $transaction instanceof WalletTransaction ? $transaction->getAmount() : $transaction;

        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Wallet balance expects a numeric amount or a WalletTransaction object.');
        }

        return $this->normalizeAmount((float) $amount);
    }

    private function normalizeAmount(int|float|string $value): float
    {
        return round((float) $value, $this->walletType?->decimals() ?? 4);
    }
}
