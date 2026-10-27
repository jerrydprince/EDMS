<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add division_id and unit_id to workflow_steps
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('division_id');
        });

        // 2. Map duplicate roles to unified roles
        // We will execute this using raw queries or Eloquent to migrate existing users and workflow_steps.
        $roleMapping = [
            'Executive Director' => 'directors',
            'Deputy Director' => 'division heads',
            'Assistant Director' => 'unit heads',
            'AGF Registry' => 'registry',
            'DA Registry' => 'registry',
            'DD APD Registry' => 'registry',
            'AD (Appointment) Registry' => 'registry',
            'Appointment Registry' => 'registry',
            'Promotion Registry' => 'registry',
            'TSW Registry' => 'registry',
            'Pool Management Registry' => 'registry',
            'AGF record manager' => 'registry',
            'DD (APD) Registry' => 'registry'
        ];

        // Ensure target roles exist
        $targetRoles = array_unique(array_values($roleMapping));
        foreach ($targetRoles as $targetRoleName) {
            $exists = DB::table('roles')->where('name', $targetRoleName)->exists();
            if (!$exists) {
                DB::table('roles')->insert([
                    'name' => $targetRoleName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        foreach ($roleMapping as $oldRoleName => $newRoleName) {
            $oldRole = DB::table('roles')->where('name', $oldRoleName)->first();
            $newRole = DB::table('roles')->where('name', $newRoleName)->first();

            if ($oldRole && $newRole) {
                // Migrate users (model_has_roles)
                DB::table('model_has_roles')
                    ->where('role_id', $oldRole->id)
                    ->update(['role_id' => $newRole->id]);

                // Update workflow_steps role_required
                DB::table('workflow_steps')
                    ->where('role_required', $oldRoleName)
                    ->update(['role_required' => $newRoleName]);

                // Delete old role
                DB::table('roles')->where('id', $oldRole->id)->delete();
                // Also clean up role_has_permissions
                DB::table('role_has_permissions')->where('role_id', $oldRole->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['division_id', 'unit_id']);
        });
        
        // Reverse role mapping is complex and not fully safe, so omitted here.
    }
};
