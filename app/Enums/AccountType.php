<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum AccountType: string
{
    use EnumHelper;
    case Current = 'current';
    case Savings = 'savings';
}
