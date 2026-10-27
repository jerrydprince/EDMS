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
        $renameMap = [
            'directors' => 'director',
            'super users' => 'super user',
            'regular users' => 'regular user',
            'secretaries' => 'secretary'
        ];

        foreach ($renameMap as $oldName => $newName) {
            DB::table('roles')->where('name', $oldName)->update(['name' => $newName]);
            
            DB::table('workflow_steps')
                ->where('role_required', $oldName)
                ->update(['role_required' => $newName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not safely reversible
    }
};
