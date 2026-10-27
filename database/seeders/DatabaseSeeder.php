<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SuperAdminSeeder::class,
            BranchSeeder::class,
            Phase10AdministrationSeeder::class,
            Phase11Adm001Seeder::class,
            Phase12Adm002Seeder::class,
            Phase13Adm004Seeder::class,
            Phase14Adm003Seeder::class,
            Phase15Adm005And006Seeder::class,
            Phase16Adm007Seeder::class,
            Phase17Adm008Seeder::class,
            AdministrationSopRulesSeeder::class,
        ]);
    }
}
