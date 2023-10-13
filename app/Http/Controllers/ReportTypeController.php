<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;

class ReportTypeController extends Controller
{
    public function index(){
        $reports = ReportType::withCount(['reports as count'])->get();

        return response()->json($reports, 200);
    }
}
