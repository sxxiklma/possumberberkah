<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions list
        $permissions = [
            // Dashboard
            'dashboard.view',

            // Master Data
            'master.products.view',
            'master.products.create',
            'master.products.edit',
            'master.products.delete',
            'master.categories.manage',
            'master.units.manage',
            'master.warehouses.manage',

            // Inventory
            'inventory.view',
            'inventory.stock-in.create',
            'inventory.stock-out.create',
            'inventory.opname.manage',
            'inventory.card.view',

            // POS / Sales
            'pos.access',
            'pos.create-sale',
            'pos.void-sale',
            'pos.return-sale',
            'pos.view-history',

            // Purchases
            'purchases.view',
            'purchases.create',
            'purchases.receive',
            'purchases.return',

            // Mitra
            'suppliers.manage',
            'customers.manage',

            // Finance
            'finance.view',
            'finance.receivables.manage',
            'finance.payables.manage',
            'finance.cash.manage',
            'finance.expenses.manage',

            // Reports
            'reports.sales',
            'reports.purchases',
            'reports.inventory',
            'reports.financial',

            // System
            'users.manage',
            'roles.manage',
            'settings.manage',
            'audit-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Super Admin: All permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::all());

        // 2. Admin: Master data, inventory, purchases, suppliers, customers, reports
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'dashboard.view',
            'master.products.view',
            'master.products.create',
            'master.products.edit',
            'master.products.delete',
            'master.categories.manage',
            'master.units.manage',
            'master.warehouses.manage',
            'inventory.view',
            'inventory.stock-in.create',
            'inventory.stock-out.create',
            'inventory.opname.manage',
            'inventory.card.view',
            'purchases.view',
            'purchases.create',
            'purchases.receive',
            'purchases.return',
            'suppliers.manage',
            'customers.manage',
            'reports.sales',
            'reports.purchases',
            'reports.inventory',
            'pos.view-history',
        ]);

        // 3. Kasir: Smart POS, sales, customer creation during sale, receipt print
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);
        $kasirRole->syncPermissions([
            'dashboard.view',
            'pos.access',
            'pos.create-sale',
            'pos.view-history',
            'pos.return-sale',
            'customers.manage',
        ]);

        // 4. Staff Gudang: stock in, stock out, opname, stock card
        $gudangRole = Role::firstOrCreate(['name' => 'Staff Gudang', 'guard_name' => 'web']);
        $gudangRole->syncPermissions([
            'dashboard.view',
            'inventory.view',
            'inventory.stock-in.create',
            'inventory.stock-out.create',
            'inventory.opname.manage',
            'inventory.card.view',
            'purchases.view',
            'purchases.receive',
        ]);

        // 5. Finance: payments, receivables, payables, cash in/out, expenses, financial reports
        $financeRole = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'web']);
        $financeRole->syncPermissions([
            'dashboard.view',
            'finance.view',
            'finance.receivables.manage',
            'finance.payables.manage',
            'finance.cash.manage',
            'finance.expenses.manage',
            'reports.sales',
            'reports.purchases',
            'reports.financial',
        ]);

        // 6. Owner / Pimpinan: executive view and all reports
        $ownerRole = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $ownerRole->syncPermissions([
            'dashboard.view',
            'reports.sales',
            'reports.purchases',
            'reports.inventory',
            'reports.financial',
            'audit-logs.view',
            'pos.view-history',
            'inventory.view',
            'inventory.card.view',
        ]);

        // Create Demo Accounts
        $usersData = [
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@sumberberkah.com',
                'phone' => '081200000001',
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Administrator Toko',
                'username' => 'admin',
                'email' => 'admin@sumberberkah.com',
                'phone' => '081200000002',
                'role' => 'Admin',
            ],
            [
                'name' => 'Kasir Utama',
                'username' => 'kasir',
                'email' => 'kasir@sumberberkah.com',
                'phone' => '081200000003',
                'role' => 'Kasir',
            ],
            [
                'name' => 'Staff Gudang',
                'username' => 'gudang',
                'email' => 'gudang@sumberberkah.com',
                'phone' => '081200000004',
                'role' => 'Staff Gudang',
            ],
            [
                'name' => 'Staff Finance',
                'username' => 'finance',
                'email' => 'finance@sumberberkah.com',
                'phone' => '081200000005',
                'role' => 'Finance',
            ],
            [
                'name' => 'Bapak Haji Berkah (Owner)',
                'username' => 'owner',
                'email' => 'owner@sumberberkah.com',
                'phone' => '081200000006',
                'role' => 'Owner',
            ],
        ];

        foreach ($usersData as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'username' => $u['username'],
                    'phone' => $u['phone'],
                    'address' => 'Jl. Merdeka No. 45, Kota Sejahtera',
                    'is_active' => true,
                    'password' => Hash::make('password'),
                ]
            );

            $user->syncRoles([$u['role']]);
        }
    }
}

