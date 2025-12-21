<?php

namespace App\Enums;

enum SectionType: string
{
    case Bank = 'bank';
    case Employee = 'employee';
    case GroupLeader = 'group_leader';
    case Bill = 'bill';
    case Loan = 'loan';
    case Other = 'other';
}
