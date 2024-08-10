<?php

namespace App\Http\Controllers;

use App\Models\WorkingDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkingDayController extends Controller
{
    //
    public function index(){
        
    }

    public function store(Request $request){
        
        //validate working days is an array with dates in the format YYYY-MM-DD
        //validate the days are in the current week
        $request->validate([
            'working_days' => 'required|array',
            'working_days.*' => 'required|date|date_format:Y-m-d|after_or_equal:'.now()->startOfWeek()->format('Y-m-d').'|before_or_equal:'.now()->endOfWeek()->format('Y-m-d')
        ]);
        $workingDays = $request->working_days;
        $user = auth()->user();
        
        try {
            DB::transaction(function () use ($workingDays, $user) {
                //get current workingdays from this week and delete
                $lastWorkingDays = $user->workingDays->filter(function ($workingDay) {
                    return Carbon::parse($workingDay->date)->isBetween(
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    );
                });
                //delete all working days from this week
                WorkingDay::destroy($lastWorkingDays->pluck('id'));

                $saveWorkingDays = [];
                //store new working days
                foreach ($workingDays as $workingDay) {
                    $saveWorkingDays[] = [
                        'user_id' => $user->id,
                        'date' => $workingDay,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                WorkingDay::insert($saveWorkingDays);
            });
            return response()->json(['message' => 'Working days updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update working days', 'error' => $e->getMessage()], 500);
        }
    }
}