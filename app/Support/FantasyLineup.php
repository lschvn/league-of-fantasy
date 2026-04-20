<?php

namespace App\Support;

class FantasyLineup
{
    // the 7 positions every lineup must have
    public const REQUIRED_POSITIONS = [
        'TOP',
        'JGL',
        'MID',
        'ADC',
        'SUP',
        'FLEX_1',
        'FLEX_2',
    ];

    // maps lineup positions to player roles (flex slots accept any role)
    public const ROLE_MAP = [
        'TOP' => 'TOP',
        'JGL' => 'JGL',
        'MID' => 'MID',
        'ADC' => 'ADC',
        'SUP' => 'SUP',
    ];

    public static function isFlex(string $position): bool
    {
        return in_array($position, ['FLEX_1', 'FLEX_2'], true);
    }
}
