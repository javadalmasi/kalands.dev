<?php

use App\Models\Admin;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Run PermissionSeeder to ensure roles and permissions exist
        (new PermissionSeeder)->run();

        $roleModels = Role::all()->keyBy('name');

        // 2. Assign roles to admins based on their current access_level
        // We use DB::table to avoid model events or missing columns if Admin model is already updated
        $admins = DB::table('admins')->get();
        foreach ($admins as $admin) {
            $oldLevel = (string) $admin->access_level;
            if (isset($roleModels[$oldLevel])) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $roleModels[$oldLevel]->id,
                    'model_type' => Admin::class,
                    'model_id' => $admin->id,
                ]);
            }
        }

        // 3. Drop access_level column
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('access_level');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('access_level', [
                'super_admin',
                'content_manager',
                'system_manager',
                'user_manager',
            ])->default('content_manager')->after('password_salt');
        });

        // Optional: restore data from roles table if possible
        $admins = Admin::all();
        foreach ($admins as $admin) {
            $role = $admin->roles()->first();
            if ($role && in_array($role->name, ['super_admin', 'content_manager', 'system_manager', 'user_manager'])) {
                $admin->update(['access_level' => $role->name]);
            }
        }
    }
};
