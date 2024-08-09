<?php

namespace App\Http\Controllers;

use App\Models\WorkingDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkingDayController extends Controller
{
    //
    public function index(){
        
    }

    public function store(Request $request){
        
        $workingDays = $request->workingDays;
        $user = auth()->user();
        DB::transaction(function () use ($workingDays, $user) {
            //get current workingdays from this week and delete
            $workingDays = $user->workingDays->filter(function ($workingDay) {
                return $workingDay->date->isBetween(
                    now()->startOfWeek(),
                    now()->endOfWeek()
                );
            });
            $workingDays->delete();
            $saveWorkingDays = [];
            //store new working days
            foreach ($workingDays as $workingDay) {
                $saveWorkingDays[] = [
                    'date' => $workingDay['date'],
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            WorkingDay::insert($saveWorkingDays);


        });
    }
}
