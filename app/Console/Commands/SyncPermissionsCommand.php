<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionRegistryService;

class SyncPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize master permission catalog across all EDMS modules and update baseline role presets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Synchronizing comprehensive permissions for all EDMS modules...');
        
        $stats = PermissionRegistryService::syncAll();
        
        $this->info("✓ Synchronization Complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Newly Created Permissions', $stats['created']],
                ['Existing Permissions Retained', $stats['existing']],
                ['Total Permissions in Catalog', $stats['total']]
            ]
        );

        $this->info('✓ Baseline role presets updated successfully.');
        return 0;
    }
}
