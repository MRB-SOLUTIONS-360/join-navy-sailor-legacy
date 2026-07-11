<?php

namespace common\static;

class Constants
{
    // Status 
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    // Candidate Type 
    const CANDIDATE_SAILOR = 1;
    const CANDIDATE_DE_SAILOR = 2;
    // const CANDIDATE_DE_ARTIFICER = 3;
    const CANDIDATE_DE_SAILOR_DOCKYARD = 3;

    const CANDIDATE_OFFICER = 5;
    const CANDIDATE_DE_OFFICER = 6;

    // marital_status
    const MARRIED = 1;
    const UNMARRIED = 2;

    // Gender 
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    // YES/NO 
    const YES = 1;
    const NO = 2;
    // YES/NO   
    const YES_ETHNIC_MINORITY = 3;


    // PAYMENT 
    const PAYMENT_PAID = 1;
    const PAYMENT_UNPAID = 2;

    // Academic Group 
    const AC_GROUP_SCIENCE = 'science';
    const AC_GROUP_BUSINESS = 'business studies';
    const AC_GROUP_HUMANITIES = 'humanities';
    const AC_GROUP_VOCATIONAL = 'vocational';
    const AC_GROUP_GENERAL = 'general';
    const AC_GROUP_MADRASHA_SCIENCE = 'madrasha_science';
    const AC_GROUP_MADRASHA_MUZABBID = 'muzabbid';

    // division 
    const DIVISION_BARISHAL = 1;
    const DIVISION_CHITTAGONG = 2;
    const DIVISION_DHAKA = 3;
    const DIVISION_KHULNA = 4;
    const DIVISION_MYMENSINGH = 5;
    const DIVISION_RAJSHAHI = 6;
    const DIVISION_RANGPUR = 7;
    const DIVISION_SYLHET = 8;
    const DIVISION_SPECIAL = 9;

    // Roll From const 
    const ROLL_FROM_BATCH = 'batch';
    const ROLL_FROM_CONFIG = 'config';

    // Payment Amount 
    const PAYMENT_AMOUNT_LIVE = 300;
    const PAYMENT_AMOUNT_SANDBOX = 10;


    //Candidate type for eligibility check 
    const ELIGIBILITY_CANDIDATE_TYPE_GENERAL = 1;
    const ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA = 2;
    const ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL = 3;

    // children-of-navy-per
    const DIST_NAVY_CHILD_SLUG = 'children-of-navy-per';
    const DIST_NOU_SCOUT_SLUG = 'nou-scout';
    // Feet to inch multiply by
    const FEET_TO_INCH_MULTI_BY = 12;
    // inch to cm multiply by
    const INCH_TO_CM_MULTI_BY = 2.54;

    /// Subject Type
    const SUBJECT_TYPE_DIPLOMA = 1;
    const SUBJECT_TYPE_TRADE = 2;

    // Batch Group 
    const GROUP_A = 1;
    const GROUP_B = 2;
    const GROUP_C = 3;
    const GROUP_D = 4;

    // Payment Type
    const PAYMENT_TYPE_ONLINE = 'online';
    const PAYMENT_TYPE_MANUAL = 'manual';

    /// Payment Live / Sandbox
    const PAYMENT_MODE_LIVE = 'live';
    const PAYMENT_MODE_SANDBOX = 'sandbox';

    //// Sailor Application Phase 
    const SAILOR_PHASE_ONE = 1; // need payment 
    const SAILOR_PHASE_TWO = 2; // need academic info 
    const SAILOR_PHASE_THREE = 3; // need personal info  
    const SAILOR_PHASE_FOUR = 4; // need application preview
    const SAILOR_PHASE_FIVE = 5; // finally submit form 


    // yes / no 
    const TEXT_YES = 'yes';
    const TEXT_NO = 'no';

    // Education Board 
    const EDU_BOARD_BARISHAL = 'barisal';
    const EDU_BOARD_CTG = 'chittagong';
    const EDU_BOARD_COMILLA = 'comilla';
    const EDU_BOARD_DHAKA = 'dhaka';
    const EDU_BOARD_DINAJPUR = 'dinajpur';
    const EDU_BOARD_JESSORE = 'jessore';
    const EDU_BOARD_MYMENSINGH = 'mymensingh';
    const EDU_BOARD_RAJSHAHI = 'rajshahi';
    const EDU_BOARD_SYLHET = 'sylhet';
    const EDU_BOARD_MARRASHA = 'madrasah';
    const EDU_BOARD_TEC = 'tec';
    const EDU_BOARD_DIBS = 'dibs';

    /// Candidate religion
    const RELIGION_MUSLIM = 1;
    const RELIGION_HINDU = 2;
    const RELIGION_CHRISTIAN = 3; /// Christian
    const RELIGION_BUDDHIST = 4;  //Buddhist
    const RELIGION_OTHER = 5;  //Other

    /// Topass primary key 
    const TOPASS_PRIMARY_KEY = 12;

    // Candidate Monitoring By
    const CAN_MONITOR_BY_IMAGE_MISSING = 'image_missinig'; // image missing in system
    const CAN_MONITOR_BY_QR = 'qr'; // image missing in system
    const CAN_MONITOR_BY_DUPLICATE_ROLL = 'duplicate_roll';  // duplicate roll
    const CAN_MONITOR_BY_ROLL_NULL = 'roll_null';  // if phase comple rull missing and date missing 
    const CAN_MONITOR_BY_MISSING_EXAM_DATE = 'missing_exam_date';  // get roll but missinig exam date missing 
    const CAN_MONITOR_BY_MISSING_EXAM_AND_ROLL_BUT_PHASE_COMPLETE = 'missing_exam_date_roll_phase_complete';  // get roll but missinig exam date missing 


    // TEAM 
    const TEAM_A = 1;
    const TEAM_B = 2;
    const TEAM_C = 3;
}
