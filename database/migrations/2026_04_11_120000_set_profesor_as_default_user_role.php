<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $this->setProfesorAsDefaultRole();
        $this->backfillProfesorRoleForUsersWithoutRoles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('profesor','estudiante') NOT NULL DEFAULT 'estudiante'");
        }
    }

    private function setProfesorAsDefaultRole(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE users MODIFY rol ENUM('profesor','estudiante') NOT NULL DEFAULT 'profesor'");
        }
    }

    private function backfillProfesorRoleForUsersWithoutRoles(): void
    {
        $profesorRoleId = DB::table('roles')
            ->where('name', 'profesor')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $profesorRoleId) {
            $profesorRoleId = DB::table('roles')->insertGetId([
                'name' => 'profesor',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $userIdsWithoutRole = DB::table('users')
            ->leftJoin('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', User::class);
            })
            ->whereNull('model_has_roles.role_id')
            ->pluck('users.id');

        if ($userIdsWithoutRole->isEmpty()) {
            return;
        }

        DB::table('users')
            ->whereIn('id', $userIdsWithoutRole)
            ->update(['rol' => 'profesor']);

        $rows = $userIdsWithoutRole
            ->map(fn ($userId) => [
                'role_id' => $profesorRoleId,
                'model_type' => User::class,
                'model_id' => $userId,
            ])
            ->all();

        DB::table('model_has_roles')->insertOrIgnore($rows);
    }
};
