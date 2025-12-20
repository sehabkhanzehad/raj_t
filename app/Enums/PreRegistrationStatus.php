<?php

namespace App\Enums;

enum PreRegistrationStatus: string
{
    case Active = 'active';
    case Registered = 'registered';
    case Archived = 'archived';
    case Transferred = 'transferred';
    case Cancelled = 'cancelled';
}

// Status Logic:

// active      = Valid, ready for main reg
// registered  = Already converted to Main Reg (cannot be registered again)
// archived    = Time over
// transferred = Ownership changed (History te new owner thakbe)
// cancelled   = Cancelled by user request