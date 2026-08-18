<?php

namespace App\Enums;

enum StageOutcome: string
{
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
}
