<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'gender' => 'male',
            'grade_level' => '2',
            'phone' => '08000000001',
            'email' => 'super_admin@ansel.com',
            'password' => Hash::make('password'),
            'is_approved' => true
        ]);

        $user = User::find(1);
        $user->assignRole('super admin');
    }
}
