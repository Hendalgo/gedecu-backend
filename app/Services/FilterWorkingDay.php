<?php

namespace App\Services;

use Carbon\Carbon;

class FilterWorkingDay
{
    protected $user;

    protected $timezone;

    public function __construct($user, $timezone)
    {
        $this->user = $user;
        $this->timezone = $timezone;
    }

    public function filterWorkingDay()
    {
        //Get just working days from the user
        //Considering just the day from this week starting from Monday and the timezone of the user

        $workingDays = $this->user->workingDays->filter(function ($workingDay) {
            return Carbon::parse($workingDay->date)->isBetween(
                now()->startOfWeek()->setTimezone($this->timezone),
                now()->endOfWeek()->setTimezone($this->timezone)
            );
        });

        return $workingDays;
    }
}
