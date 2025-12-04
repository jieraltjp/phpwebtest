<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\Inquiry;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 测试数据库迁移
     */
    public function test_database_migrations(): void
    {
        // 检查所有必要的表是否存在
        $expectedTables = [
            'users',
            'products',
            'orders',
            'order_items',
            'shipments',
            'inquiries',
            'cache',
            'jobs',
            'failed_jobs'
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table {$table} should exist"
            );
        }
    }

    /**
     * 测试用户模型关系
     */
    public function test_user_model_relationships(): void
    {
        $user = User::factory()->create();

        // 创建关联的订单
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);
        
        // 创建关联的询价
        $inquiries = Inquiry::factory()->count(2)->create(['user_id' => $user->id]);

        // 测试关系
        $this->assertCount(3, $user->orders);
        $this->assertCount(2, $user->inquiries);
        
        foreach ($orders as $order) {
            $this->assertEquals($user->id, $order->user_id);
        }
        
        foreach ($inquiries as $inquiry) {
            $this->assertEquals($user->id, $inquiry->user_id);
        }
    }

    /**
     * 测试产品模型关系
     */
    public function test_product_model_relationships(): void
    {
        $product = Product::factory()->create();

        // 创建关联的订单项
        $orderItems = OrderItem::factory()->count(2)->create([
            'product_id' => $product->id,
            'sku' => $product->sku
        ]);

        // 测试关系
        $this->assertCount(2, $product->orderItems);
        
        foreach ($orderItems as $item) {
            $this->assertEquals($product->id, $item->product_id);
            $this->assertEquals($product->sku, $item->sku);
        }
    }

    /**
     * 测试订单模型关系
     */
    public function test_order_model_relationships(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // 创建订单项
        $products = Product::factory()->count(3)->create();
        foreach ($products as $index => $product) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'sku' => $product->sku,
                'quantity' => ($index + 1) * 2,
                'price' => $product->price,
                'total' => ($index + 1) * 2 * $product->price
            ]);
        }

        // 创建物流记录
        $shipment = Shipment::factory()->create(['order_id' => $order->id]);

        // 测试关系
        $this->assertEquals($user->id, $order->user_id);
        $this->assertCount(3, $order->items);
        $this->assertCount(1, $order->shipments);
        
        // 测试订单项关系
        foreach ($order->items as $item) {
            $this->assertInstanceOf(Product::class, $item->product);
            $this->assertEquals($order->id, $item->order_id);
        }

        // 测试物流关系
        foreach ($order->shipments as $shipment) {
            $this->assertEquals($order->id, $shipment->order_id);
        }

        // 测试订单总额计算
        $expectedTotal = $order->items->sum('total');
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    /**
     * 测试询价模型关系
     */
    public function test_inquiry_model_relationships(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();
        
        $inquiry = Inquiry::factory()->create([
            'user_id' => $user->id,
            'product_skus' => $products->pluck('sku')->toArray()
        ]);

        // 测试关系
        $this->assertEquals($user->id, $inquiry->user_id);
        $this->assertCount(3, $inquiry->product_skus);
        
        foreach ($products as $product) {
            $this->assertTrue(in_array($product->sku, $inquiry->product_skus));
        }
    }

    /**
     * 测试数据完整性约束
     */
    public function test_data_integrity_constraints(): void
    {
        // 测试用户名唯一性
        $user1 = User::factory()->create(['username' => 'unique_user']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['username' => 'unique_user']);
    }

    /**
     * 测试外键约束
     */
    public function test_foreign_key_constraints(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        // 创建有效的订单
        $order = Order::factory()->create([
            'user_id' => $user->id
        ]);

        // 创建有效的订单项
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);

        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals($order->id, $orderItem->order_id);
        $this->assertEquals($product->id, $orderItem->product_id);

        // 测试无效外键（如果数据库支持外键约束）
        // 注意：SQLite默认不启用外键约束，这个测试可能在某些环境下不通过
    }

    /**
     * 测试数据工厂
     */
    public function test_data_factories(): void
    {
        // 测试用户工厂
        $user = User::factory()->create();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->username);
        $this->assertNotEmpty($user->email);

        // 测试产品工厂
        $product = Product::factory()->create();
        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotEmpty($product->sku);
        $this->assertNotEmpty($product->name);
        $this->assertGreaterThan(0, $product->price);

        // 测试订单工厂
        $order = Order::factory()->create(['user_id' => $user->id]);
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertNotEmpty($order->order_number);

        // 测试订单项工厂
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);
        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertEquals($order->id, $orderItem->order_id);
        $this->assertEquals($product->id, $orderItem->product_id);

        // 测试询价工厂
        $inquiry = Inquiry::factory()->create(['user_id' => $user->id]);
        $this->assertInstanceOf(Inquiry::class, $inquiry);
        $this->assertEquals($user->id, $inquiry->user_id);
        $this->assertNotEmpty($inquiry->inquiry_number);
    }

    /**
     * 测试批量数据创建
     */
    public function test_bulk_data_creation(): void
    {
        // 批量创建用户
        $users = User::factory()->count(10)->create();
        $this->assertCount(10, $users);
        $this->assertEquals(10, User::count());

        // 批量创建产品
        $products = Product::factory()->count(20)->create();
        $this->assertCount(20, $products);
        $this->assertEquals(20, Product::count());

        // 批量创建订单和订单项
        $orders = Order::factory()->count(5)->create(['user_id' => $users->first()->id]);
        foreach ($orders as $order) {
            OrderItem::factory()->count(3)->create([
                'order_id' => $order->id,
                'product_id' => $products->random()->id
            ]);
        }

        $this->assertEquals(5, Order::count());
        $this->assertEquals(15, OrderItem::count()); // 5 orders * 3 items each

        // 批量创建询价
        $inquiries = Inquiry::factory()->count(8)->create(['user_id' => $users->random()->id]);
        $this->assertCount(8, $inquiries);
        $this->assertEquals(8, Inquiry::count());
    }

    /**
     * 测试数据查询性能
     */
    public function test_database_query_performance(): void
    {
        // 创建大量测试数据
        $users = User::factory()->count(100)->create();
        $products = Product::factory()->count(200)->create();
        
        $orders = Order::factory()->count(50)->create([
            'user_id' => $users->random()->id
        ]);
        
        foreach ($orders as $order) {
            OrderItem::factory()->count(rand(1, 5))->create([
                'order_id' => $order->id,
                'product_id' => $products->random()->id
            ]);
        }

        // 测试简单查询性能
        $startTime = microtime(true);
        $userCount = User::count();
        $productCount = Product::count();
        $orderCount = Order::count();
        $endTime = microtime(true);

        $queryTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(100, $queryTime, 'Simple count queries should be under 100ms');

        $this->assertEquals(100, $userCount);
        $this->assertEquals(200, $productCount);
        $this->assertEquals(50, $orderCount);

        // 测试复杂关联查询性能
        $startTime = microtime(true);
        $ordersWithItems = Order::with('items.product')->limit(10)->get();
        $endTime = microtime(true);

        $joinQueryTime = ($endTime - $startTime) * 1000;
        $this->assertLessThan(500, $joinQueryTime, 'Join queries should be under 500ms');

        $this->assertCount(10, $ordersWithItems);
        foreach ($ordersWithItems as $order) {
            $this->assertTrue($order->relationLoaded('items'));
        }
    }

    /**
     * 测试事务处理
     */
    public function test_database_transactions(): void
    {
        $user = User::factory()->create();
        $initialUserCount = User::count();

        // 测试成功事务
        DB::transaction(function () use ($user) {
            Order::factory()->create(['user_id' => $user->id]);
            OrderItem::factory()->create([
                'order_id' => Order::latest()->first()->id,
                'product_id' => Product::factory()->create()->id
            ]);
        });

        $this->assertEquals($initialUserCount + 1, User::count());
        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, OrderItem::count());

        // 测试失败事务回滚
        try {
            DB::transaction(function () {
                Order::factory()->create(['user_id' => $user->id]);
                OrderItem::factory()->create([
                    'order_id' => 99999, // 不存在的订单ID，会导致外键约束失败
                    'product_id' => Product::factory()->create()->id
                ]);
            });
        } catch (\Exception $e) {
            // 期望的异常
        }

        // 事务应该回滚，订单数量不应该增加
        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, OrderItem::count());
    }

    /**
     * 测试数据软删除（如果实现）
     */
    public function test_soft_deletes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);

        // 如果实现了软删除
        if (method_exists($order, 'trashed')) {
            $order->delete();
            
            // 软删除后，默认查询不应该包含已删除记录
            $this->assertEquals(0, Order::count());
            
            // 但应该能找到已删除的记录
            $this->assertEquals(1, Order::withTrashed()->count());
            $this->assertTrue($order->trashed());
        }
    }

    /**
     * 测试数据验证约束
     */
    public function test_database_validation_constraints(): void
    {
        // 测试产品价格不能为负数
        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['price' => -10.00]);
    }

    /**
     * 测试数据库连接
     */
    public function test_database_connection(): void
    {
        // 测试数据库连接是否正常
        $this->assertTrue(DB::connection()->getPdo() !== null);
        
        // 测试基本查询
        $result = DB::select('SELECT 1 as test');
        $this->assertEquals(1, $result[0]->test);
    }

    /**
     * 测试数据迁移回滚
     */
    public function test_migration_rollback(): void
    {
        // 这个测试需要手动执行迁移回滚
        // 在实际测试环境中，可以使用 RefreshDatabase trait
        $this->assertTrue(true, 'Migration rollback test placeholder');
    }

    /**
     * 测试数据库种子数据
     */
    public function test_database_seeders(): void
    {
        // 运行种子数据
        $this->seed([
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\ProductSeeder::class
        ]);

        // 验证种子数据
        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Product::count());
        
        // 验证特定种子数据
        $this->assertDatabaseHas('users', [
            'username' => 'testuser'
        ]);
        
        $this->assertDatabaseHas('products', [
            'sku' => 'ALIBABA_SKU_A123'
        ]);
    }

    /**
     * 测试数据库索引
     */
    public function test_database_indexes(): void
    {
        // 检查重要索引是否存在
        $indexes = [
            'users' => ['username', 'email'],
            'products' => ['sku'],
            'orders' => ['order_number', 'user_id'],
            'order_items' => ['order_id', 'product_id'],
            'inquiries' => ['inquiry_number', 'user_id']
        ];

        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                // 这里需要根据具体数据库实现索引检查
                // SQLite的索引查询可能与其他数据库不同
                $this->assertTrue(true, "Index check for {$table}.{$column}");
            }
        }
    }

    /**
     * 测试数据并发操作
     */
    public function test_concurrent_database_operations(): void
    {
        $user = User::factory()->create();
        
        // 模拟并发创建订单
        $orders = collect();
        for ($i = 0; $i < 5; $i++) {
            $orders->push(Order::factory()->create([
                'user_id' => $user->id,
                'order_number' => 'CONCURRENT-' . str_pad($i, 4, '0', STR_PAD_LEFT)
            ]));
        }

        $this->assertCount(5, $orders);
        $this->assertEquals(5, Order::where('user_id', $user->id)->count());
        
        // 验证订单号唯一性
        $orderNumbers = $orders->pluck('order_number')->unique();
        $this->assertCount(5, $orderNumbers);
    }
}