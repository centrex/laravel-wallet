<?php

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
            $table->uuid();

            $table->string('name', 36)->default('default');
            $table->decimal('balance', 12, 4)->default(0.00);
            $table->tinyInteger('wallet_type_id')->default(1);
            $table->nullableMorphs('user');
            $table->string('currency_code', 3)->default('BDT');

            $table->timestamps();
            $table->softDeletes();
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
