<?php

declare(strict_types = 1);

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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            $table->string('name', 36)->default(config('wallet.default_name', 'default'));
            $table->decimal('balance', 12, 4)->default(0.00);
            $table->tinyInteger('wallet_type_id')->default((int) config('wallet.default_wallet_type', 1))->index();
            $table->nullableMorphs('user');
            $table->string('currency_code', 3)->default(config('wallet.default_currency', 'BDT'));

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_type', 'user_id', 'wallet_type_id'], 'wallet_owner_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
