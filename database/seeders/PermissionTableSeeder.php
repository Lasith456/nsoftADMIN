<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
           'role-list',
           'role-create',
           'role-edit',
           'role-delete',
           'product-list',
           'product-create',
           'product-edit',
           'product-delete',
           'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
            'supplier-list',
           'supplier-create',
            'supplier-edit',
            'supplier-delete',
            'vehicle-list',
            'vehicle-create',
            'vehicle-edit',
            'vehicle-delete',
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            'subdepartment-list',
            'subdepartment-create',
            'subdepartment-edit',
            'subdepartment-delete',
            'agent-list',
            'agent-create',
            'agent-edit',
            'agent-delete',
            'grn-list',
            'grn-create',
            'grn-delete',
            'grn-manage',
            'purchase-order-list',
            'purchase-order-create',
            'purchase-order-edit',
            'purchase-order-delete',
            'receive-note-list',
            'receive-note-create',
            'receive-note-show',
            'receive-note-edit',
            'receive-note-delete',
            'delivery-note-list',
            'delivery-note-create',
            'delivery-note-show',
            'delivery-note-manage',
            'delivery-note-delete',
            'stock-management',
            'invoice-list',
            'invoice-create',
            'invoice-edit',
            'invoice-delete',
            'payment-create',
            'payment-history',
            'report-view',
            'company-list',
            'company-create',
            'company-edit',
            'company-delete',
            'user-list',
           'user-edit',
           'user-delete',
        ];

        foreach ($permissions as $permission) {
             Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

//php artisan db:seed --class=PermissionTableSeeder