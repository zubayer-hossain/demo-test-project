<?php

namespace App\Enums;

enum DemoTestInquiryStatus: string
{
    case ACTIVE = 'ACTIVE';
    case PROCESSED = 'PROCESSED';
    case FAILED = 'FAILED';
}
