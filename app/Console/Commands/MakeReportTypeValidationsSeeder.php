<?php

namespace App\Console\Commands;

use App\Models\ReportTypeValidations;
use Illuminate\Console\Command;

class MakeReportTypeValidationsSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-report-type-validations-seeder';

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
        $validations = ReportTypeValidations::all();

        $file = fopen(database_path('seeders/ReportTypeValidationsSeeder.php'), 'w');

        fwrite($file, "<?php\n\n");
        fwrite($file, "namespace Database\Seeders;\n\n");
        fwrite($file, "use Illuminate\Database\Seeder;\n");
        fwrite($file, "use App\Models\ReportTypeValidations;\n\n");
        fwrite($file, "class ReportTypeValidationsSeeder extends Seeder\n");
        fwrite($file, "{\n");
        fwrite($file, "    public function run()\n");
        fwrite($file, "    {\n");
        fwrite($file, "        ReportTypeValidations::query()->delete();\n\n");

        foreach ($validations as $validation) {
            fwrite($file, "        ReportTypeValidations::create([\n");
            fwrite($file, "            'name' => '{$validation->name}',\n");
            fwrite($file, "            'validation' => '{$validation->validation}',\n");
            fwrite($file, "            'validation_role' => '{$validation->validation_role}',\n");
            fwrite($file, "            'report_type_id' => {$validation->report_type_id},\n");
            fwrite($file, "        ]);\n");
        }

        fwrite($file, "    }\n");
        fwrite($file, "}\n");
        
        fclose($file);

        $this->info('Seeder created successfully.');
    }
}
