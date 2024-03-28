<?php

namespace App\Console\Commands;

use App\Http\Controllers\InconsistenceController;
use App\Models\Report;
use App\Services\KeyValueMap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInconsistences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-inconsistences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = DB::transaction(function () {
            /**Empty Inconsistences table */
            DB::table('inconsistences')->truncate();
            $reports = Report::with('subreports.data')->get();
            foreach ($reports as $report) {
                /**Format subreport data */
                $subreports = (new KeyValueMap())->transformElement($report->subreports);
                $newInconsistences = new InconsistenceController();
                $newInconsistences->check_inconsistences($report, $subreports);
            }
        });
    }
}
