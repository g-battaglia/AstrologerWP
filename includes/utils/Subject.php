<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace AstrologerWP\Utils;

/**
 * Class Subject
 *
 * Represents a subject with astrological data.
 */
class Subject {
    public string $name;
    public int $year;
    public int $month;
    public int $day;
    public int $hour;
    public int $minute;
    public int $second;
    public float $longitude;
    public float $latitude;
    public string $city;
    public string $nation;
    public string $timezone;
    public string $zodiacType;
    public string $houseSystem;
    public string $siderealMode;
    public string $perspectiveType;

    /**
     * Subject constructor.
     *
     * @param string $name
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param float $longitude
     * @param float $latitude
     * @param string $city
     * @param string $nation
     * @param string $timezone
     * @param string $zodiacType
     * @param string $houseSystem
     * @param string $siderealMode
     * @param string $perspectiveType
     * @param int $second
     */
    public function __construct(
        string $name,
        int $year,
        int $month,
        int $day,
        int $hour,
        int $minute,
        float $longitude,
        float $latitude,
        string $city,
        string $nation,
        string $timezone,
        string $zodiacType,
        string $houseSystem,
        string $siderealMode,
        string $perspectiveType,
        int $second = 0
    ) {

        if ($zodiacType === 'Tropical' && isset($siderealMode)) {
            unset($siderealMode);
        }

        $this->name = $name;
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->hour = $hour;
        $this->minute = $minute;
        $this->second = $second;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->city = $city;
        $this->nation = $nation;
        $this->timezone = $timezone;
        $this->zodiacType = $zodiacType;
        $this->houseSystem = $houseSystem;
        $this->siderealMode = $siderealMode ?? '';
        $this->perspectiveType = $perspectiveType;
    }

    /**
     * Convert the subject data to an array.
     *
     * @return array
     */
    public function toArray(): array {
        $data = [
            'name' => $this->name,
            'year' => $this->year,
            'month' => $this->month,
            'day' => $this->day,
            'hour' => $this->hour,
            'minute' => $this->minute,
            'second' => $this->second,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'city' => $this->city,
            'nation' => $this->nation,
            'timezone' => $this->timezone,
            'zodiac_type' => $this->zodiacType,
            'houses_system_identifier' => $this->houseSystem,
            'perspective_type' => $this->perspectiveType
        ];

        if (!empty($this->siderealMode) && $this->zodiacType === 'Sidereal') {
            $data['sidereal_mode'] = $this->siderealMode;
        }

        return $data;
    }
}
