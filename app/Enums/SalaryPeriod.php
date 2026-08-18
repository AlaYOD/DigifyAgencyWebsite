<?php

namespace App\Enums;

enum SalaryPeriod: string
{
    case HOUR = 'hour';
    case MONTH = 'month';
    case YEAR = 'year';
}
