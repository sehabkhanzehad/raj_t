<?php

namespace App\Enums;

enum SectionType: string
{
    case Bank = 'bank';
    case Employee = 'employee';
    case GroupLeader = 'group_leader';
    case Bill = 'bill';
    case Lend = 'lend';
    case Borrow = 'borrow';
        // case PreRegistration = 'pre_registration';
        // case Registration = 'registration';
    case Other = 'other';
}
