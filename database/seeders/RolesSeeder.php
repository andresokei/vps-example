<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar cache de permisos antes de crear
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos
        $permisos = [
            'ver grupos',
            'crear grupos',
            'editar grupos',
            'eliminar grupos',
            'asignar tests',
            'ver analisis',
            'exportar pdf',
            'gestionar usuarios',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // Rol: profesor
        $profesor = Role::firstOrCreate(['name' => 'profesor', 'guard_name' => 'web']);
        $profesor->syncPermissions([
            'ver grupos',
            'crear grupos',
            'editar grupos',
            'asignar tests',
            'ver analisis',
            'exportar pdf',
        ]);

        // Rol: admin (todos los permisos)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());
    }
}
