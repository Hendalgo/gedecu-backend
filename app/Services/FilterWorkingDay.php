<?php
namespace App\Services;

use Carbon\Carbon;

class FilterWorkingDay
{   
    protected $user;

    public function __construct($user)
    {
      $this->user = $user;
    }
    public function filterWorkingDay()
    {
      //Get just working days from the user
      //Considering just the day from this week starting from Monday

      $workingDays = $this->user->workingDays->filter(function ($workingDay) {
          return Carbon::parse($workingDay->date)->isBetween(
              now()->startOfWeek(),
              now()->endOfWeek()
          );
      });

      return $workingDays;
    }
}