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
            $workingDayDate = Carbon::parse($workingDay->date)->format('Y-m-d');
            $startOfWeek = now($this->timezone)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = now($this->timezone)->endOfWeek(Carbon::SUNDAY);
            return Carbon::create($workingDayDate)->isBetween($startOfWeek, $endOfWeek);
        });
        return $workingDays->toArray();
    }
}
