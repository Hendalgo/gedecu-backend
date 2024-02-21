<?php

namespace App\Console\Commands;

use App\Models\Subreport;
use App\Models\SubreportData;
use Illuminate\Console\Command;

class TransferSubreportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-subreport-data';

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
        $subreports = Subreport::all();

        foreach ($subreports as $subreport) {
            $data = json_decode($subreport->data, true);
        
            foreach ($data as $key => $value) {
                SubreportData::create([
                    'subreport_id' => $subreport->id,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }
        $this->info('Data transfer completed successfully.');
    }
}
