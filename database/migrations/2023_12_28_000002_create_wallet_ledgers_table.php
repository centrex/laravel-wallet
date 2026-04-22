<?php

declare(strict_types = 1);

use Centrex\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('wallet.ledger_table', 'wallet_ledgers'), function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->nullableMorphs('transaction');
            $table->decimal('amount', 12, 4);
            $table->decimal('running_balance', 12, 4);
            $table->foreignIdFor(Wallet::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['wallet_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('wallet.ledger_table', 'wallet_ledgers'));
    }
};
