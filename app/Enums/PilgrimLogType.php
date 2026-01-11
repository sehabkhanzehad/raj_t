<?php

namespace App\Enums;

enum PilgrimLogType: string
{
    case UmrahRegistered = 'umrah_registered'; // ✅

        //Todo: think when implement it, keep store before status, when change status
    case UmrahCancelled = 'umrah_cancelled';
    case UmrahCompleted = 'umrah_completed';

        // Todo: think when implement it, keep store before status, when change status
    case HajjPreRegistered = 'hajj_pre_registered';
    case HajjRegistered = 'hajj_registered';
    // case HajjPreRegArchived = 'hajj_archived';
    // case HajjPreRegCancelled = 'hajj_pre_reg_cancelled';
    // case HajjPreRegTransferred = 'hajj_pre_reg_transferred';

    // case HajjRegCancelled = 'hajj_reg_cancelled';
    // case HajjRegTransferred = 'hajj_reg_transferred';
    // case HajjCompleted = 'hajj_completed';
}

//create pendting then dont create log
//when active create log
// tODO: IF PAID AMOUNT THEN ALSO ADD LOG, AND CONNECT WITH TRANSACTIONS 
