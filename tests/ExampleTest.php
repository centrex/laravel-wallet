<?php

declare(strict_types = 1);

use Centrex\Wallet\Traits\HasWallet;
use Centrex\Wallet\Wallet as WalletService;
use Illuminate\Database\Eloquent\Model;

it('creates a default wallet and records ledger entries for deposits and withdrawals', function (): void {
    $user = TestWalletUser::query()->create(['name' => 'Alice']);
    $wallet = $user->wallet();

    expect($wallet)->not->toBeNull()
        ->and((float) $wallet->balance)->toBe(0.0);

    $wallet->incrementBalance(50);
    $wallet->decrementBalance(20);
    $wallet->refresh();

    expect((float) $wallet->balance)->toBe(30.0)
        ->and($wallet->walletLedgers()->count())->toBe(2)
        ->and((float) $wallet->walletLedgers()->latest('id')->first()->running_balance)->toBe(30.0);
});

it('prevents overdrafts and supports transfers through the wallet service', function (): void {
    $alice = TestWalletUser::query()->create(['name' => 'Alice']);
    $bob = TestWalletUser::query()->create(['name' => 'Bob']);

    $aliceWallet = $alice->wallet();
    $bobWallet = $bob->wallet();

    $aliceWallet->incrementBalance(25);

    expect(fn () => $aliceWallet->decrementBalance(30))->toThrow(RuntimeException::class);

    app(WalletService::class)->transfer($aliceWallet, $bobWallet, 10);

    expect((float) $aliceWallet->fresh()->balance)->toBe(15.0)
        ->and((float) $bobWallet->fresh()->balance)->toBe(10.0);
});

class TestWalletUser extends Model
{
    use HasWallet;

    protected $table = 'users';

    protected $guarded = [];
}
