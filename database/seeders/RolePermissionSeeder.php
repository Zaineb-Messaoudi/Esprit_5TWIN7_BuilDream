<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Equipment management
            'view equipment',
            'create equipment',
            'edit equipment',
            'delete equipment',

            // Rental management
            'view rentals',
            'create rentals',
            'edit rentals',
            'delete rentals',
            'approve rentals',
            'reject rentals',

            // Contract management
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            'sign contracts',

            // Payment management
            'view payments',
            'process payments',
            'refund payments',

            // Admin settings
            'view admin settings',
            'edit admin settings',

            // Analytics
            'view analytics',
            'export analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->givePermissionTo([
            'view equipment',
            'create equipment',
            'edit equipment',
            'delete equipment',
            'view rentals',
            'view contracts',
            'view payments',
            'view analytics',
        ]);

        $buyer = Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'web']);
        $buyer->givePermissionTo([
            'view equipment',
            'view rentals',
            'view contracts',
            'view payments',
        ]);

        // User role (default)
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->givePermissionTo([
            'view equipment',
        ]);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
