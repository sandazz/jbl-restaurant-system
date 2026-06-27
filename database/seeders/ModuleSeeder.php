<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Customer Management',
                'description' => 'Manage customers and customer information',
                'icon' => 'users',
                'route' => 'customers.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Category Management',
                'description' => 'Manage product categories',
                'icon' => 'tags',
                'route' => 'categories.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Employee Management',
                'description' => 'Manage employees and staff',
                'icon' => 'user-tie',
                'route' => 'employees.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inventory & Products',
                'description' => 'Manage products and inventory',
                'icon' => 'boxes',
                'route' => 'inventory.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Supplier Management',
                'description' => 'Manage suppliers and purchases',
                'icon' => 'truck',
                'route' => 'suppliers.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wastage Management',
                'description' => 'Track product wastage and losses',
                'icon' => 'trash-alt',
                'route' => 'wastage.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'POS & Billing',
                'description' => 'Point of sale and billing management',
                'icon' => 'cash-register',
                'route' => 'pos.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reports',
                'description' => 'Generate and view reports',
                'icon' => 'chart-bar',
                'route' => 'reports.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Settings',
                'description' => 'System settings and configuration',
                'icon' => 'cog',
                 'route' => 'settings.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shift & Till',
                'description' => 'Cashier shift and till reconciliation',
                'icon' => 'balance-scale',
                'route' => 'clerk-balancings.index',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('modules')->upsert(
            $modules,
            ['name'],
            ['description', 'icon', 'route', 'updated_at']
        );

        $adminId = DB::table('roles')->where('name', 'Admin')->first()->id;
        $managerId = DB::table('roles')->where('name', 'Manager')->first()->id;
        $cashierId = DB::table('roles')->where('name', 'Cashier')->first()->id;

        $allModules = DB::table('modules')->get();
        $posModuleId = DB::table('modules')->where('name', 'POS & Billing')->first()->id;
        $shiftModuleId = DB::table('modules')->where('name', 'Shift & Till')->first()->id;

        foreach ($allModules as $module) {
            DB::table('role_module')->insertOrIgnore([
                'role_id' => $adminId,
                'module_id' => $module->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($module->id !== $posModuleId) {
                DB::table('role_module')->insertOrIgnore([
                    'role_id' => $managerId,
                    'module_id' => $module->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($module->id === $posModuleId || $module->id === $shiftModuleId) {
                DB::table('role_module')->insertOrIgnore([
                    'role_id' => $cashierId,
                    'module_id' => $module->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Grant Cashier access to Order History module as well
        $orderHistoryModule = DB::table('modules')->where('name', 'Order History')->first();
        if ($orderHistoryModule) {
            DB::table('role_module')->insertOrIgnore([
                'role_id' => $cashierId,
                'module_id' => $orderHistoryModule->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
