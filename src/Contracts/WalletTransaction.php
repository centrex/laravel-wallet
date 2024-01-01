<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Contracts;

interface WalletTransaction
{
    public function getAmount();
}
