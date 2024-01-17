<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Models;

use Centrex\Wallet\Contracts\WalletTransaction;
use Centrex\Wallet\Enums\WalletType;
use Centrex\Wallet\Traits\{HasUuid, Trashed};
use Exception;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

final class Wallet extends Model
{
    use HasUuid;
    use SoftDeletes;
    use Trashed;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'wallet_type_id',
        'balance',
        'user_id',
        'user_type',
    ];

    public function user()
    {
        return $this->morphTo('user');
    }

    public function walletType()
    {
        return $this->belongsTo(WalletType::cases());
    }

    public function walletLedgers()
    {
        return $this->hasMany(WalletLedger::class);
    }

    public function getBalanceAttribute()
    {
        return $this->balance;
    }

    /**
     * @param  $transaction  WalletTransaction|integer|float|double
     *
     * @throws Exception
     */
    public function incrementBalance($transaction): self
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletTypeInteger($transaction);
            $this->increment('balance', $amount);
            $this->createWalletLedgerEntry($amount, $this->balance);

            return $this;
        }

        if (!$transaction instanceof WalletTransaction) {
            throw new Exception('Increment balance expects parameter to be a float or a WalletTransaction object.');
        }

        $this->increment('balance', $transaction->getAmount());

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->balance);

        return $this;
    }

    /**
     * @param  $transaction  WalletTransaction|integer|float|double
     *
     * @throws Exception
     */
    public function decrementBalance($transaction): self
    {
        if (is_numeric($transaction)) {
            $amount = $this->convertToWalletTypeInteger($transaction);
            $this->decrement('raw_balance', $amount);
            $this->createWalletLedgerEntry($amount, $this->balance, 'decrement');

            return $this;
        }

        if (!$transaction instanceof WalletTransaction) {
            throw new Exception('Decrement balance expects parameter to be a number or a WalletTransaction object.');
        }

        $this->decrement('balance', $transaction->getAmount());

        // Record in ledger
        $this->createWalletLedgerEntry($transaction, $this->raw_balance, 'decrement');

        return $this;
    }

    /**
     * @return mixed
     *
     * @throws Exception
     */
    private function createWalletLedgerEntry($transaction, $newRunningRawBalance, string $type = 'increment')
    {
        if (is_numeric($transaction)) {
            if ($type === 'decrement') {
                $transaction = -$transaction;
            }

            return WalletLedger::query()->create([
                'wallet_id'           => $this->id,
                'amount'              => $transaction,
                'running_raw_balance' => $newRunningRawBalance,
            ]);
        }

        if (!$transaction instanceof WalletTransaction) {
            throw new Exception('Wallet ledger entries expect first parameter to be numeric or a WalletTransaction instance');
        }

        $amount = $this->convertToWalletTypeInteger($transaction->getAmount());

        if ($type === 'decrement') {
            $amount = -$amount;
        }

        return WalletLedger::query()->create([
            'wallet_id'           => $this->id,
            'transaction_id'      => $transaction->id,
            'transaction_type'    => $transaction::class,
            'amount'              => $amount,
            'running_raw_balance' => $newRunningRawBalance,
        ]);
    }

    /**
     * Converts the given value to an integer that is compatible with this wallet's type.
     *
     * @param  int  $value
     * @return float|int
     */
    private function convertToWalletTypeInteger($value)
    {
        if (empty($this->walletType) || $this->walletType->decimals === 0) {
            return $value;
        }

        return (int) ($value * 10 ** $this->walletType->decimals);
    }
}
