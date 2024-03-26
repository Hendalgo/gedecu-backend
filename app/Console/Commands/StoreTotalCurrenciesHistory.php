<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Models\TotalCurrenciesHistory;
use Illuminate\Console\Command;
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
            $currency_history = TotalCurrenciesHistory::where('currency_id', $currency->currency_id)
                ->latest('created_at')
                ->first();
            if (!$currency_history) {
                TotalCurrenciesHistory::create([
                    'currency_id' => $currency->currency_id,
                    'total' => $currency->total,
                ]);
            }
            else if ($currency_history->total != $currency->total) {
                TotalCurrenciesHistory::create([
                    'currency_id' => $currency->currency_id,
                    'total' => $currency->total,
                ]);
            }
        }
        Log::info('Finished total currencies history storage');
    }
}
