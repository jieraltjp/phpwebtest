<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清空现有权限数据
        DB::table('permissions')->delete();
        
        // 定义默认权限
        $permissions = [
            // 用户管理权限
            ['name' => '查看用户', 'slug' => 'view-users', 'group' => '用户管理', 'description' => '查看用户列表和详情'],
            ['name' => '创建用户', 'slug' => 'create-users', 'group' => '用户管理', 'description' => '创建新用户账户'],
            ['name' => '编辑用户', 'slug' => 'edit-users', 'group' => '用户管理', 'description' => '编辑用户信息'],
            ['name' => '删除用户', 'slug' => 'delete-users', 'group' => '用户管理', 'description' => '删除用户账户'],
            ['name' => '激活用户', 'slug' => 'activate-users', 'group' => '用户管理', 'description' => '激活或停用用户账户'],
            ['name' => '管理用户权限', 'slug' => 'manage-user-permissions', 'group' => '用户管理', 'description' => '分配和管理用户权限'],
            
            // 角色管理权限
            ['name' => '查看角色', 'slug' => 'view-roles', 'group' => '角色管理', 'description' => '查看角色列表和详情'],
            ['name' => '创建角色', 'slug' => 'create-roles', 'group' => '角色管理', 'description' => '创建新角色'],
            ['name' => '编辑角色', 'slug' => 'edit-roles', 'group' => '角色管理', 'description' => '编辑角色信息和权限'],
            ['name' => '删除角色', 'slug' => 'delete-roles', 'group' => '角色管理', 'description' => '删除角色'],
            ['name' => '分配角色', 'slug' => 'assign-roles', 'group' => '角色管理', 'description' => '为用户分配角色'],
            
            // 权限管理权限
            ['name' => '查看权限', 'slug' => 'view-permissions', 'group' => '权限管理', 'description' => '查看权限列表'],
            ['name' => '创建权限', 'slug' => 'create-permissions', 'group' => '权限管理', 'description' => '创建新权限'],
            ['name' => '编辑权限', 'slug' => 'edit-permissions', 'group' => '权限管理', 'description' => '编辑权限信息'],
            ['name' => '删除权限', 'slug' => 'delete-permissions', 'group' => '权限管理', 'description' => '删除权限'],
            
            // 产品管理权限
            ['name' => '查看产品', 'slug' => 'view-products', 'group' => '产品管理', 'description' => '查看产品列表和详情'],
            ['name' => '创建产品', 'slug' => 'create-products', 'group' => '产品管理', 'description' => '添加新产品'],
            ['name' => '编辑产品', 'slug' => 'edit-products', 'group' => '产品管理', 'description' => '编辑产品信息'],
            ['name' => '删除产品', 'slug' => 'delete-products', 'group' => '产品管理', 'description' => '删除产品'],
            ['name' => '管理产品库存', 'slug' => 'manage-product-stock', 'group' => '产品管理', 'description' => '管理产品库存数量'],
            
            // 订单管理权限
            ['name' => '查看订单', 'slug' => 'view-orders', 'group' => '订单管理', 'description' => '查看订单列表和详情'],
            ['name' => '创建订单', 'slug' => 'create-orders', 'group' => '订单管理', 'description' => '创建新订单'],
            ['name' => '编辑订单', 'slug' => 'edit-orders', 'group' => '订单管理', 'description' => '编辑订单信息'],
            ['name' => '删除订单', 'slug' => 'delete-orders', 'group' => '订单管理', 'description' => '删除订单'],
            ['name' => '更新订单状态', 'slug' => 'update-order-status', 'group' => '订单管理', 'description' => '更新订单状态'],
            ['name' => '管理订单发货', 'slug' => 'manage-order-shipping', 'group' => '订单管理', 'description' => '管理订单发货和物流'],
            
            // 询价管理权限
            ['name' => '查看询价', 'slug' => 'view-inquiries', 'group' => '询价管理', 'description' => '查看询价列表和详情'],
            ['name' => '创建询价', 'slug' => 'create-inquiries', 'group' => '询价管理', 'description' => '创建新询价'],
            ['name' => '编辑询价', 'slug' => 'edit-inquiries', 'group' => '询价管理', 'description' => '编辑询价信息'],
            ['name' => '删除询价', 'slug' => 'delete-inquiries', 'group' => '询价管理', 'description' => '删除询价'],
            ['name' => '回复询价', 'slug' => 'reply-inquiries', 'group' => '询价管理', 'description' => '回复客户询价'],
            
            // 批量采购权限
            ['name' => '查看批量采购', 'slug' => 'view-bulk-purchase', 'group' => '批量采购', 'description' => '查看批量采购订单'],
            ['name' => '创建批量采购', 'slug' => 'create-bulk-purchase', 'group' => '批量采购', 'description' => '创建批量采购订单'],
            ['name' => '管理批量采购折扣', 'slug' => 'manage-bulk-discount', 'group' => '批量采购', 'description' => '管理批量采购折扣策略'],
            
            // 系统设置权限
            ['name' => '查看系统设置', 'slug' => 'view-system-settings', 'group' => '系统设置', 'description' => '查看系统配置'],
            ['name' => '编辑系统设置', 'slug' => 'edit-system-settings', 'group' => '系统设置', 'description' => '修改系统配置'],
            ['name' => '查看系统日志', 'slug' => 'view-system-logs', 'group' => '系统设置', 'description' => '查看系统运行日志'],
            ['name' => '管理缓存', 'slug' => 'manage-cache', 'group' => '系统设置', 'description' => '管理系统缓存'],
            
            // 报表统计权限
            ['name' => '查看销售报表', 'slug' => 'view-sales-reports', 'group' => '报表统计', 'description' => '查看销售数据报表'],
            ['name' => '查看用户报表', 'slug' => 'view-user-reports', 'group' => '报表统计', 'description' => '查看用户统计报表'],
            ['name' => '查看产品报表', 'slug' => 'view-product-reports', 'group' => '报表统计', 'description' => '查看产品统计报表'],
            ['name' => '导出报表', 'slug' => 'export-reports', 'group' => '报表统计', 'description' => '导出各类报表'],
            
            // 财务管理权限
            ['name' => '查看财务数据', 'slug' => 'view-financial-data', 'group' => '财务管理', 'description' => '查看财务数据'],
            ['name' => '管理退款', 'slug' => 'manage-refunds', 'group' => '财务管理', 'description' => '处理退款申请'],
            ['name' => '查看发票', 'slug' => 'view-invoices', 'group' => '财务管理', 'description' => '查看发票信息'],
            ['name' => '创建发票', 'slug' => 'create-invoices', 'group' => '财务管理', 'description' => '创建发票'],
            
            // API管理权限
            ['name' => '查看API文档', 'slug' => 'view-api-docs', 'group' => 'API管理', 'description' => '查看API文档'],
            ['name' => '管理API密钥', 'slug' => 'manage-api-keys', 'group' => 'API管理', 'description' => '管理API访问密钥'],
            ['name' => '查看API日志', 'slug' => 'view-api-logs', 'group' => 'API管理', 'description' => '查看API访问日志'],
            
            // 通知管理权限
            ['name' => '发送通知', 'slug' => 'send-notifications', 'group' => '通知管理', 'description' => '发送系统通知'],
            ['name' => '管理通知模板', 'slug' => 'manage-notification-templates', 'group' => '通知管理', 'description' => '管理通知模板'],
            ['name' => '查看通知历史', 'slug' => 'view-notification-history', 'group' => '通知管理', 'description' => '查看通知发送历史'],
        ];
        
        // 插入权限数据
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'group' => $permission['group'],
                'description' => $permission['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info('权限数据创建成功！');
    }
}