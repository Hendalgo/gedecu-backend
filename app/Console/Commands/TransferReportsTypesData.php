<?php

namespace App\Console\Commands;

use App\Models\ReportType;
use App\Models\ReportTypeValidations;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class TransferReportsTypesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-reports-types-data';

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
        $reportTypes = ReportType::all();
        foreach ($reportTypes as $reportType) {
            $data = collect(json_decode($reportType->meta_data, true));
            $data->map(function ($item, $key) use ($reportType, $data) {
                $userRoleIds = Role::pluck('id')->toArray(); //Get all role ids

                if ($key !== 'all' && !in_array($key, $userRoleIds)) {
                    return; //Skip if role id is not found thats mean is not a validation
                }
                $validations = [];

                collect($item)->map(function ($validation) use ($reportType, $key, &$validations) {
                    $validations[] = [
                        'name' => $validation['name'],
                        'validation' => $validation['validation'],
                        'validation_role' => $key,
                        'report_type_id' => $reportType->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                });
                //Before inserting the validations, delete the old ones
                //ReportTypeValidations::where('report_type_id', $reportType->id)->where('validation_role', $key)->delete();

                ReportTypeValidations::insert($validations);

                // Remove the key from the data
                $data->forget($key);
            });

            // Update the meta_data with the modified data
            $reportType->meta_data = $data->toJson();
            $reportType->save();
        }
    }
}
