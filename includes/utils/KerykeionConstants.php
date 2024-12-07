<?php

namespace AstrologerWP\Constants;

class KerykeionConstants {
    public const ZODIAC_TYPES = [
        'Tropic',
        'Sidereal'
    ];

    public const SIGNS = [
        'Ari',
        'Tau',
        'Gem',
        'Can',
        'Leo',
        'Vir',
        'Lib',
        'Sco',
        'Sag',
        'Cap',
        'Aqu',
        'Pis'
    ];

    public const SIGN_NUMBERS = [
        0,
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11
    ];

    public const HOUSES = [
        'First_House',
        'Second_House',
        'Third_House',
        'Fourth_House',
        'Fifth_House',
        'Sixth_House',
        'Seventh_House',
        'Eighth_House',
        'Ninth_House',
        'Tenth_House',
        'Eleventh_House',
        'Twelfth_House'
    ];

    public const HOUSE_NUMBERS = [
        1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        11,
        12
    ];

    public const PLANETS = [
        'Sun',
        'Moon',
        'Mercury',
        'Venus',
        'Mars',
        'Jupiter',
        'Saturn',
        'Uranus',
        'Neptune',
        'Pluto',
        'Mean_Node',
        'True_Node',
        'Mean_South_Node',
        'True_South_Node',
        'Chiron',
        'Mean_Lilith'
    ];

    public const ELEMENTS = [
        'Air',
        'Fire',
        'Earth',
        'Water'
    ];

    public const QUALITIES = [
        'Cardinal',
        'Fixed',
        'Mutable'
    ];

    public const CHART_TYPES = [
        'Natal',
        'ExternalNatal',
        'Synastry',
        'Transit'
    ];

    public const POINT_TYPES = [
        'Planet',
        'House'
    ];

    public const LUNAR_PHASE_EMOJIS = [
        '🌑',
        '🌒',
        '🌓',
        '🌔',
        '🌕',
        '🌖',
        '🌗',
        '🌘'
    ];

    public const LUNAR_PHASE_NAMES = [
        'New Moon',
        'Waxing Crescent',
        'First Quarter',
        'Waxing Gibbous',
        'Full Moon',
        'Waning Gibbous',
        'Last Quarter',
        'Waning Crescent'
    ];

    public const SIDEREAL_MODES = [
        'FAGAN_BRADLEY',
        'LAHIRI',
        'DELUCE',
        'RAMAN',
        'USHASHASHI',
        'KRISHNAMURTI',
        'DJWHAL_KHUL',
        'YUKTESHWAR',
        'JN_BHASIN',
        'BABYL_KUGLER1',
        'BABYL_KUGLER2',
        'BABYL_KUGLER3',
        'BABYL_HUBER',
        'BABYL_ETPSC',
        'ALDEBARAN_15TAU',
        'HIPPARCHOS',
        'SASSANIAN',
        'J2000',
        'J1900',
        'B1950'
    ];

    public const HOUSES_SYSTEM_IDENTIFIERS = [
        'A' => 'equal',
        'B' => 'Alcabitius',
        'C' => 'Campanus',
        'D' => 'equal (MC)',
        'F' => 'Carter poli-equ.',
        'H' => 'horizon/azimut',
        'I' => 'Sunshine',
        'i' => 'Sunshine/alt.',
        'K' => 'Koch',
        'L' => 'Pullen SD',
        'M' => 'Morinus',
        'N' => 'equal/1=Aries',
        'O' => 'Porphyry',
        'P' => 'Placidus',
        'Q' => 'Pullen SR',
        'R' => 'Regiomontanus',
        'S' => 'Sripati',
        'T' => 'Polich/Page',
        'U' => 'Krusinski-Pisa-Goelzer',
        'V' => 'equal/Vehlow',
        'W' => 'equal/whole sign',
        'X' => 'axial rotation system/Meridian houses',
        'Y' => 'APC houses'
    ];

    public const PERSPECTIVE_TYPES = [
        'Apparent Geocentric',
        'Heliocentric',
        'Topocentric',
        'True Geocentric'
    ];

    public const SIGNS_EMOJIS = [
        '♈️',
        '♉️',
        '♊️',
        '♋️',
        '♌️',
        '♍️',
        '♎️',
        '♏️',
        '♐️',
        '♑️',
        '♒️',
        '♓️'
    ];

    public const KERYKEION_CHART_THEMES = [
        'light',
        'dark',
        'dark-high-contrast',
        'classic'
    ];

    public const KERYKEION_CHART_LANGUAGES = [
        'EN',
        'FR',
        'PT',
        'IT',
        'CN',
        'ES',
        'RU',
        'TR',
        'DE',
        'HI'
    ];

    public const RELATIONSHIP_SCORE_DESCRIPTIONS = [
        'Minimal',
        'Medium',
        'Important',
        'Very Important',
        'Exceptional',
        'Rare Exceptional'
    ];
}
