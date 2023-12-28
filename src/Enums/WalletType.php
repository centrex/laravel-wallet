<?php

declare(strict_types = 1);

namespace Centrex\Wallet\Enums;

use Illuminate\Support\Str;

enum WalletType: int
{
    case DEFAULT  = 1;
    case TEMPORAY = 2;

    public function getName(): string
    {
        return __(Str::studly($this->name));
    }

    public function getValue()
    {
        return $this->value;
    }

    public static function getLabel($value): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->getValue() === $value) {
                return $case->getName();
            }
        }

        return null;
    }
}
