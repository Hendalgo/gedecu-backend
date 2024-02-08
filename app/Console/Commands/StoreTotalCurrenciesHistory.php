<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BankAccount;
use App\Models\TotalCurrenciesHistory;
use Illuminate\Support\Facades\Log;

class StoreTotalCurrenciesHistory extends Command
{
    protected $signature = 'totalcurrencieshistory:store';

    protected $description = 'Store total currencies history';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currencies = BankAccount::where('delete', false)
            ->selectRaw('currency_id, SUM(balance) as total')
            ->groupBy('currency_id')
            ->get();

        foreach ($currencies as $currency) {
            TotalCurrenciesHistory::create([
                'currency_id' => $currency->currency_id,
                'total' => $currency->total,
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute()
            ]);
        }
        Log::info('Finished total currencies history storage');
    }
    
}