<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'Admin')->first()->id;
        $managerRoleId = DB::table('roles')->where('name', 'Manager')->first()->id;
        $cashierRoleId = DB::table('roles')->where('name', 'Cashier')->first()->id;

        DB::table('users')->upsert([
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('adminjbl@2026'),
                'role_id' => $adminRoleId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@gmail.com',
                'password' => Hash::make('cashier@2026'),
                'role_id' => $cashierRoleId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['email'], ['name', 'role_id', 'status', 'updated_at']);

        DB::table('employees')->upsert([
            [
                'user_id' => 1,
                'phone' => '555-0001',
                'address' => '123 Admin Street',
                'city' => 'Restaurant City',
                'state' => 'RC',
                'postal_code' => '12345',
                'hire_date' => now()->subMonths(12),
                'salary' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'phone' => '555-0002',
                'address' => '789 Cashier Road',
                'city' => 'Restaurant City',
                'state' => 'RC',
                'postal_code' => '12345',
                'hire_date' => now()->subMonths(3),
                'salary' => 2500.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['user_id'], ['phone', 'address', 'city', 'state', 'postal_code', 'hire_date', 'salary', 'updated_at']);
    }
}
