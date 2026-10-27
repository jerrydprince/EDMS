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
        // 1. Rename existing roles
        $renameMap = [
            'division heads' => 'deputy director',
            'unit heads' => 'assistant director',
            'Executive Secretariat Staff' => 'secretaries'
        ];

        foreach ($renameMap as $oldName => $newName) {
            DB::table('roles')->where('name', $oldName)->update(['name' => $newName]);
            
            // Also update workflow_steps just in case
            DB::table('workflow_steps')
                ->where('role_required', $oldName)
                ->update(['role_required' => $newName]);
        }

        // 2. Merge data entry into record manager
        $dataEntryRole = DB::table('roles')->where('name', 'data entry')->first();
        $recordManagerRole = DB::table('roles')->where('name', 'record manager')->first();

        if ($dataEntryRole && $recordManagerRole) {
            // Assign users who had data entry to record manager if they don't already have it
            $dataEntryUsers = DB::table('model_has_roles')
                                ->where('role_id', $dataEntryRole->id)
                                ->get();
                                
            foreach ($dataEntryUsers as $userRole) {
                $exists = DB::table('model_has_roles')
                            ->where('role_id', $recordManagerRole->id)
                            ->where('model_id', $userRole->model_id)
                            ->where('model_type', $userRole->model_type)
                            ->exists();
                            
                if (!$exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $recordManagerRole->id,
                        'model_id' => $userRole->model_id,
                        'model_type' => $userRole->model_type
                    ]);
                }
            }

            // Remove data entry assignments
            DB::table('model_has_roles')->where('role_id', $dataEntryRole->id)->delete();
            
            // Delete data entry role
            DB::table('roles')->where('id', $dataEntryRole->id)->delete();
            
            // Update workflow_steps
            DB::table('workflow_steps')
                ->where('role_required', 'data entry')
                ->update(['role_required' => 'record manager']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is not safe without restoring deleted rows from a backup.
    }
};
