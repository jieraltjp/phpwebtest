<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清空现有角色数据
        DB::table('roles')->delete();
        DB::table('role_permissions')->delete();
        
        // 定义默认角色
        $roles = [
            [
                'name' => '超级管理员',
                'slug' => 'super_admin',
                'level' => 1,
                'description' => '系统超级管理员，拥有所有权限',
                'is_active' => true,
            ],
            [
                'name' => '管理员',
                'slug' => 'admin',
                'level' => 2,
                'description' => '系统管理员，拥有大部分管理权限',
                'is_active' => true,
            ],
            [
                'name' => '销售经理',
                'slug' => 'sales_manager',
                'level' => 3,
                'description' => '销售经理，负责订单和客户管理',
                'is_active' => true,
            ],
            [
                'name' => '产品经理',
                'slug' => 'product_manager',
                'level' => 3,
                'description' => '产品经理，负责产品管理',
                'is_active' => true,
            ],
            [
                'name' => '销售员',
                'slug' => 'sales_staff',
                'level' => 4,
                'description' => '销售员，负责处理订单和客户询价',
                'is_active' => true,
            ],
            [
                'name' => '客服代表',
                'slug' => 'customer_service',
                'level' => 4,
                'description' => '客服代表，负责客户服务和询价回复',
                'is_active' => true,
            ],
            [
                'name' => '财务人员',
                'slug' => 'finance_staff',
                'level' => 4,
                'description' => '财务人员，负责财务管理',
                'is_active' => true,
            ],
            [
                'name' => '企业客户',
                'slug' => 'enterprise_customer',
                'level' => 5,
                'description' => '企业客户，可以查看价格和下订单',
                'is_active' => true,
            ],
            [
                'name' => '普通客户',
                'slug' => 'regular_customer',
                'level' => 5,
                'description' => '普通客户，可以浏览产品和询价',
                'is_active' => true,
            ],
        ];
        
        // 创建角色
        $createdRoles = [];
        foreach ($roles as $role) {
            $createdRole = Role::create([
                'name' => $role['name'],
                'slug' => $role['slug'],
                'level' => $role['level'],
                'description' => $role['description'],
                'is_active' => $role['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $createdRoles[$role['slug']] = $createdRole;
        }
        
        // 为角色分配权限
        $this->assignPermissionsToRoles($createdRoles);
        
        $this->command->info('角色数据创建成功！');
    }
    
    /**
     * 为角色分配权限
     */
    private function assignPermissionsToRoles(array $roles): void
    {
        // 获取所有权限
        $permissions = Permission::all()->keyBy('slug');
        
        // 超级管理员拥有所有权限
        if (isset($roles['super_admin'])) {
            $roles['super_admin']->permissions()->attach($permissions->pluck('id'));
        }
        
        // 管理员权限（除了系统核心设置）
        if (isset($roles['admin'])) {
            $adminPermissions = $permissions->except([
                'edit-system-settings', // 不能修改系统核心设置
            ]);
            $roles['admin']->permissions()->attach($adminPermissions->pluck('id'));
        }
        
        // 销售经理权限
        if (isset($roles['sales_manager'])) {
            $salesManagerPermissions = [
                'view-users', 'edit-users', // 查看和编辑用户
                'view-roles', // 查看角色
                'view-permissions', // 查看权限
                'view-products', 'edit-products', // 查看和编辑产品
                'view-orders', 'create-orders', 'edit-orders', 'update-order-status', 'manage-order-shipping', // 订单管理
                'view-inquiries', 'create-inquiries', 'edit-inquiries', 'reply-inquiries', // 询价管理
                'view-bulk-purchase', 'create-bulk-purchase', 'manage-bulk-discount', // 批量采购
                'view-sales-reports', 'view-user-reports', 'view-product-reports', 'export-reports', // 报表统计
                'view-financial-data', 'manage-refunds', 'view-invoices', 'create-invoices', // 财务管理
                'send-notifications', 'manage-notification-templates', 'view-notification-history', // 通知管理
            ];
            $this->attachPermissionsBySlugs($roles['sales_manager'], $salesManagerPermissions, $permissions);
        }
        
        // 产品经理权限
        if (isset($roles['product_manager'])) {
            $productManagerPermissions = [
                'view-products', 'create-products', 'edit-products', 'delete-products', 'manage-product-stock', // 产品管理
                'view-orders', // 查看订单
                'view-inquiries', // 查看询价
                'view-product-reports', // 产品报表
                'view-api-docs', // API文档
            ];
            $this->attachPermissionsBySlugs($roles['product_manager'], $productManagerPermissions, $permissions);
        }
        
        // 销售员权限
        if (isset($roles['sales_staff'])) {
            $salesStaffPermissions = [
                'view-products', // 查看产品
                'view-orders', 'create-orders', 'edit-orders', // 订单管理
                'view-inquiries', 'create-inquiries', 'edit-inquiries', 'reply-inquiries', // 询价管理
                'view-bulk-purchase', 'create-bulk-purchase', // 批量采购
                'view-sales-reports', // 销售报表
                'send-notifications', // 发送通知
            ];
            $this->attachPermissionsBySlugs($roles['sales_staff'], $salesStaffPermissions, $permissions);
        }
        
        // 客服代表权限
        if (isset($roles['customer_service'])) {
            $customerServicePermissions = [
                'view-products', // 查看产品
                'view-orders', // 查看订单
                'view-inquiries', 'create-inquiries', 'edit-inquiries', 'reply-inquiries', // 询价管理
                'send-notifications', 'manage-notification-templates', 'view-notification-history', // 通知管理
            ];
            $this->attachPermissionsBySlugs($roles['customer_service'], $customerServicePermissions, $permissions);
        }
        
        // 财务人员权限
        if (isset($roles['finance_staff'])) {
            $financeStaffPermissions = [
                'view-orders', // 查看订单
                'view-financial-data', 'manage-refunds', 'view-invoices', 'create-invoices', // 财务管理
                'view-sales-reports', 'export-reports', // 报表统计
            ];
            $this->attachPermissionsBySlugs($roles['finance_staff'], $financeStaffPermissions, $permissions);
        }
        
        // 企业客户权限
        if (isset($roles['enterprise_customer'])) {
            $enterpriseCustomerPermissions = [
                'view-products', // 查看产品
                'create-orders', 'view-orders', // 创建和查看自己的订单
                'create-inquiries', 'view-inquiries', // 创建和查看自己的询价
                'view-bulk-purchase', 'create-bulk-purchase', // 批量采购
            ];
            $this->attachPermissionsBySlugs($roles['enterprise_customer'], $enterpriseCustomerPermissions, $permissions);
        }
        
        // 普通客户权限
        if (isset($roles['regular_customer'])) {
            $regularCustomerPermissions = [
                'view-products', // 查看产品
                'create-inquiries', 'view-inquiries', // 创建和查看自己的询价
            ];
            $this->attachPermissionsBySlugs($roles['regular_customer'], $regularCustomerPermissions, $permissions);
        }
    }
    
    /**
     * 根据权限标识符数组为角色分配权限
     */
    private function attachPermissionsBySlugs(Role $role, array $permissionSlugs, $permissions): void
    {
        $permissionIds = [];
        foreach ($permissionSlugs as $slug) {
            if (isset($permissions[$slug])) {
                $permissionIds[] = $permissions[$slug]->id;
            }
        }
        
        if (!empty($permissionIds)) {
            $role->permissions()->attach($permissionIds);
        }
    }
}