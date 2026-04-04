<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Yasumi\Yasumi;

class HolidayService
{
    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $locale;

    public function __construct()
    {
        $this->country = config('holidays.country', 'Hungary');
        $this->locale = config('holidays.locale', 'hu_HU');
    }

    /**
     * Calculate the number of working days in a period, excluding weekends and holidays.
     */
    public function getWorkingDays(CarbonInterface $start, CarbonInterface $end): int
    {
        $period = CarbonPeriod::create($start, $end);
        $count = 0;

        // Group dates by year to minimize Yasumi calls
        $years = [];
        foreach ($period as $date) {
            $years[$date->year][] = $date;
        }

        foreach ($years as $year => $dates) {
            $holidays = Yasumi::create($this->country, $year, $this->locale);

            foreach ($dates as $date) {
                if ($date->isWeekend()) {
                    continue;
                }

                if ($holidays->isHoliday($date)) {
                    continue;
                }

                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if a given date is a holiday.
     */
    public function isHoliday(CarbonInterface $date): bool
    {
        $holidays = Yasumi::create($this->country, $date->year, $this->locale);

        return $holidays->isHoliday($date);
    }

    /**
     * Get all holidays for a given year.
     */
    public function getHolidaysForYear(int $year): array
    {
        $holidays = Yasumi::create($this->country, $year, $this->locale);

        return $holidays->getHolidays();
    }
}
