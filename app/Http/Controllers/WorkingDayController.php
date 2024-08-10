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

        //validate working days is an array with dates in the format YYYY-MM-DD
        $request->validate([
            'workingDays' => 'required|array',
            'workingDays.*' => 'required|date_format:Y-m-d',
        ]);

        DB::transaction(function () use ($workingDays, $user) {
            //get current workingdays from this week and delete
            $workingDays = $user->workingDays->filter(function ($workingDay) {
                return $workingDay->date->isBetween(
                    now()->startOfWeek(),
                    now()->endOfWeek()
                );
            });
            //delete all working days from this week
            WorkingDay::destroy($workingDays->pluck('id'));

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
        return response()->json(['message' => 'Working days updated successfully'], 200);
    }
}
