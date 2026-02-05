<?php

namespace App\Enums;

enum SectionType: string
{
    case Bank = 'bank';
    case Employee = 'employee';
    case GroupLeader = 'group_leader';
    case Bill = 'bill';
    case Lend = 'lend'; // Need to create when subscribe
    case Borrow = 'borrow'; // Need to create when subscribe
    case PreRegistration = 'pre_registration'; // Need to create when subscribe
    case Registration = 'registration'; // Need to create when subscribe
    case UmrahCost = 'umrah_cost'; // Need to create when subscribe
    case Other = 'other';
}
