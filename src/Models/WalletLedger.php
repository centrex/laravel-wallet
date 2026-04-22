<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphTo};

final class WalletLedger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date',
        'transaction_id',
        'transaction_type',
        'amount',
        'running_balance',
        'wallet_id',
    ];

    protected $casts = [
        'date'            => 'date',
        'amount'          => 'decimal:4',
        'running_balance' => 'decimal:4',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction(): MorphTo
    {
        return $this->morphTo('transaction');
    }
}
