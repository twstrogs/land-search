<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class InitSettings extends Command
{
    protected $signature = 'app:init-settings';
    protected $description = 'Initialize default settings in database';

    public function handle(): int
    {
        $this->info('Initializing settings...');
        
        Setting::initDefaults();
        
        $this->info('Settings initialized successfully!');
        
        return Command::SUCCESS;
    }
}
