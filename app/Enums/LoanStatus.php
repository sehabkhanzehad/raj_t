<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Paid = 'paid';
    case Unpaid = 'unpaid';
    case Partial = 'partial';
}
