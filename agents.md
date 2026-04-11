# agents.md

## Agent Guidance — laravel-wallet

### Package Purpose
Digital wallet with an immutable transaction ledger. Any model (User, Business, etc.) can hold a wallet with a balance maintained by credit/debit transactions.

### Before Making Changes
- Read `src/Models/Wallet.php` — holds the balance and owner reference
- Read `src/Traits/HasWallet.php` — the trait API exposed to host models
- Read `src/Enums/` — transaction types (credit, debit, refund, etc.)
- Read `src/Contracts/` — wallet interface for type-safe DI

### Critical Invariants — Never Break
- **Transactions are immutable**: once created, a transaction record must not be updated or deleted
- **Balance is derived from transactions**: if `balance` is a cached column, it must reconcile with the transaction sum
- **No negative balance by default**: `withdraw()` must throw if balance is insufficient (unless overdraft is enabled via config)
- **Atomicity**: deposit and withdraw must run inside a DB transaction to prevent race conditions

### Common Tasks

**Adding a new transaction type**
1. Add to `Enums/` transaction type enum
2. Add a dedicated method on `Wallet` (e.g., `refund()`)
3. Ensure balance calculations handle the new type
4. Add tests including edge cases (zero amount, insufficient balance)

**Adding wallet-to-wallet transfers**
1. Wrap in a DB transaction: debit source, credit destination atomically
2. Create two transaction records linked by a `transfer_id`
3. Roll back both on any failure

**Adding multi-currency support**
- Add a `currency` column to `wallets` table (nullable, default from config)
- Each wallet holds one currency — do not mix currencies in one wallet

### Testing
```sh
composer test:unit
composer test:types
composer test:lint
```

Always test balance consistency:
```php
$wallet->deposit(1000);
$wallet->withdraw(400);
expect($wallet->fresh()->balance)->toBe(600);
```

### Safe Operations
- Adding new transaction type enum values
- Adding new wallet methods (non-breaking)
- Adding nullable migration columns
- Adding model scopes

### Risky Operations — Confirm Before Doing
- Changing how `balance` is calculated or cached
- Changing `deposit()` / `withdraw()` method signatures
- Adding soft deletes to the transactions table (immutability concern)

### Do Not
- Update or delete transaction records — they are a ledger
- Perform balance changes outside a DB transaction
- Return a stale cached balance without refreshing
- Skip `declare(strict_types=1)` in any new file
