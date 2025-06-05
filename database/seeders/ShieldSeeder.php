<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[
            {
                "name":"super_admin",
                "guard_name":"web",
                "permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role"]
            },
            {
                "name":"platform_head",
                "guard_name":"web",
                "permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","manage_platform","strategic_planning","coordination"]
            },
            {
                "name":"scientific_project_manager",
                "guard_name":"web",
                "permissions":["view_role","view_any_role","project_oversight","quality_control","expert_support","manage_research_projects"]
            },
            {
                "name":"routine_measurement_technician",
                "guard_name":"web",
                "permissions":["conduct_measurements","sample_preparation","instrument_operation","view_measurements","create_measurements"]
            },
            {
                "name":"technical_specialist",
                "guard_name":"web",
                "permissions":["manage_self_service_instruments","solid_state_measurements","hr_mas_measurements","data_management","logs_system_access"]
            },
            {
                "name":"administrative_support",
                "guard_name":"web",
                "permissions":["administrative_tasks","user_management","scheduling","reporting","general_support"]
            },
            {
                "name":"application_support",
                "guard_name":"web",
                "permissions":["view_role","view_any_role","general_admin_access"]
            }
        ]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
