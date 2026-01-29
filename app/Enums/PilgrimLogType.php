<?php

namespace App\Enums;

enum PilgrimLogType: string
{
    case UmrahRegistered = 'umrah_registered'; // ✅
    case UmrahCancelled = 'umrah_cancelled'; // ✅
    case UmrahCompleted = 'umrah_completed'; // ✅

    case HajjPreRegistered = 'hajj_pre_registered'; // ✅
    case HajjRegistered = 'hajj_registered'; // ✅
    case HajjPreRegArchived = 'hajj_pre_reg_archived'; // ✅
    case HajjPreRegCancelled = 'hajj_pre_reg_cancelled'; // ✅
    case HajjPreRegTransferred = 'hajj_pre_reg_transferred'; // ✅

    // case HajjCompleted = 'hajj_completed';
}

//create pendting then dont create log
//when active create log
// tODO: IF PAID AMOUNT THEN ALSO ADD LOG, AND CONNECT WITH TRANSACTIONS 
