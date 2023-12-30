<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Centrex\Wallet\Wallet
 */
final class Wallet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Centrex\Wallet\Wallet::class;
    }
}
