<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Commands;

use Illuminate\Console\Command;

final class WalletCommand extends Command
{
    public $signature = 'laravel-wallet';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
