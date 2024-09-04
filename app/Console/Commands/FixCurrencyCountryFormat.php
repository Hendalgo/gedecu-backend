<?php

namespace App\Console\Commands;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Console\Command;

class FixCurrencyCountryFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-currency-country-format';

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
        $currencies = Currency::all();
        $banks = Bank::all();

        foreach ($banks as $bank) {
            $currency = $currencies->where('country_id', $bank->country_id)->first();
            $bank->update([
                'currency_id' => $currency->id,
            ]);
            $this->info("Currency updated for $bank->name Bank.");
        }
    }
}
