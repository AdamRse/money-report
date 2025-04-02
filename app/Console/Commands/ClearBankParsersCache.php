<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBankParsersCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank-parsers:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cache of bank parsers list. The folder "app/Services/BankParsers" will be scanned again on next use of Bank Parser.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Cache::forget('bank.parsers.list');
        $this->info('Bank parsers cache cleared successfully!');
    }
}
