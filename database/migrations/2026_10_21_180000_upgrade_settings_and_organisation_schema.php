<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            if (!Schema::hasColumn('organisations', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('organisations', 'website')) {
                $table->string('website')->nullable()->after('address');
            }
            if (!Schema::hasColumn('organisations', 'rc_number')) {
                $table->string('rc_number')->nullable()->after('website');
            }
            if (!Schema::hasColumn('organisations', 'tin_number')) {
                $table->string('tin_number')->nullable()->after('rc_number');
            }
            if (!Schema::hasColumn('organisations', 'motto')) {
                $table->string('motto')->nullable()->after('tin_number');
            }
            if (!Schema::hasColumn('organisations', 'timezone')) {
                $table->string('timezone')->default('Africa/Lagos')->after('motto');
            }
            if (!Schema::hasColumn('organisations', 'currency_symbol')) {
                $table->string('currency_symbol')->default('₦')->after('timezone');
            }
            if (!Schema::hasColumn('organisations', 'currency_code')) {
                $table->string('currency_code')->default('NGN')->after('currency_symbol');
            }
            if (!Schema::hasColumn('organisations', 'stamp_path')) {
                $table->string('stamp_path')->nullable()->after('currency_code');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'density')) {
                $table->string('density')->default('comfortable')->after('font_size');
            }
            if (!Schema::hasColumn('users', 'default_landing_page')) {
                $table->string('default_landing_page')->default('/dashboard')->after('density');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Africa/Lagos')->after('date_format');
            }
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('NGN')->after('timezone');
            }
        });

        // Seed default organisation if none exists
        if (DB::table('organisations')->count() === 0) {
            DB::table('organisations')->insert([
                'company_name' => 'Office of the Accountant-General of the Federation (OAGF)',
                'email' => 'support@oagf.gov.ng',
                'phone' => '09074713739',
                'address' => 'Treasury House, Samuel Ladoke Akintola Boulevard, Garki II, Abuja, Nigeria',
                'website' => 'https://www.oagf.gov.ng',
                'rc_number' => 'FED-MIN-FIN-001',
                'tin_number' => 'TIN-OAGF-992100',
                'motto' => 'Integrity, Transparency and Excellence in Public Financial Management',
                'primary_color' => '#10B981',
                'secondary_color' => '#0F172A',
                'timezone' => 'Africa/Lagos',
                'currency_symbol' => '₦',
                'currency_code' => 'NGN',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'website', 'rc_number', 'tin_number', 'motto',
                'timezone', 'currency_symbol', 'currency_code', 'stamp_path'
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['density', 'default_landing_page', 'timezone', 'currency']);
        });
    }
};
