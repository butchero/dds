<?php

namespace App\Console\Commands;

use App\Support\BuderusCatalog;
use Illuminate\Console\Command;

class ImportBuderusCatalog extends Command
{
    protected $signature = 'catalog:import-buderus';

    protected $description = 'Înlocuiește catalogul cu categoriile și produsele Buderus';

    public function handle(BuderusCatalog $catalog): int
    {
        $catalog->replace($this->output);

        return self::SUCCESS;
    }
}
