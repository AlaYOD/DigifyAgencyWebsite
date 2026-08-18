<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case INTERNSHIP = 'internship';
    case TEMPORARY = 'temporary';

    public function schemaValue(): string
    {
        return match ($this) {
            self::FULL_TIME => 'FULL_TIME',
            self::PART_TIME => 'PART_TIME',
            self::CONTRACT => 'CONTRACTOR',
            self::INTERNSHIP => 'INTERN',
            self::TEMPORARY => 'TEMPORARY',
        };
    }
}
