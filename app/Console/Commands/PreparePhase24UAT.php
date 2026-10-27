<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\Phase10AdministrationSeeder;
use Database\Seeders\Phase11Adm001Seeder;
use Database\Seeders\Phase12Adm002Seeder;
use Database\Seeders\Phase13Adm004Seeder;
use Database\Seeders\Phase14Adm003Seeder;
use Database\Seeders\Phase15Adm005006Seeder;
use Database\Seeders\Phase16Adm007Seeder;
use Database\Seeders\Phase17Adm008Seeder;
use Database\Seeders\Phase24UATSeeder;

class PreparePhase24UAT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uat:prepare-phase24';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepares the Phase 24 UAT Environment (Seeds actors and required workflows)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Phase 24 UAT Preparation...');

        $seeders = [
            Phase10AdministrationSeeder::class,
            Phase11Adm001Seeder::class,
            Phase12Adm002Seeder::class,
            Phase13Adm004Seeder::class,
            Phase14Adm003Seeder::class,
            Phase15Adm005006Seeder::class,
            Phase16Adm007Seeder::class,
            Phase17Adm008Seeder::class,
            Phase24UATSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->info("Running Seeder: {$seeder}");
            try {
                Artisan::call('db:seed', ['--class' => $seeder]);
                $this->info(Artisan::output());
            } catch (\Exception $e) {
                $this->error("Error running {$seeder}: " . $e->getMessage());
            }
        }

        $this->info('Phase 24 UAT Environment is ready.');
        $this->info('You can now log in using the provisioned accounts (e.g., agf.registry@oagf.gov.ng, da@oagf.gov.ng) with password: password123');
    }
}
