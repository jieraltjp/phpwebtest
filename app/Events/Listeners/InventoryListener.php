<?php

namespace App\Events\Listeners;

use App\Events\AbstractListener;
use App\Events\Contracts\EventInterface;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderCancelledEvent;
use App\Events\Product\ProductUpdatedEvent;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InventoryListener extends AbstractListener
{
    protected int $priority = 7;
    protected array $supportedEvents = [
        OrderCreatedEvent::class,
        OrderCancelledEvent::class,
        ProductUpdatedEvent::class,
    ];

    public function handle(EventInterface $event): void
    {
        $this->safeHandle($event, function ($event) {
            switch (true) {
                case $event instanceof OrderCreatedEvent:
                    $this->handleOrderCreated($event);
                    break;
                case $event instanceof OrderCancelledEvent:
                    $this->handleOrderCancelled($event);
                    break;
                case $event instanceof ProductUpdatedEvent:
                    $this->handleProductUpdated($event);
                    break;
                default:
                    $this->log($event, 'Inventory: No handler for event type');
            }
        });
    }

    protected function handleOrderCreated(OrderCreatedEvent $event): void
    {
        DB::transaction(function () use ($event) {
            $lowStockProducts = [];
            $outOfStockProducts = [];

            foreach ($event->getItems() as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    $this->logError($event, "Product not found: {$item['product_id']}");
                    continue;
                }

                $oldStock = $product->stock_quantity;
                $newStock = $oldStock - $item['quantity'];

                if ($newStock < 0) {
                    $this->logError($event, "Insufficient stock for product {$product->sku}", null);
                    throw new \Exception("Insufficient stock for product {$product->sku}");
                }

                // 更新库存
                $product->stock_quantity = $newStock;
                $product->save();

                // 记录库存变化
                $this->recordInventoryChange(
                    $product->id,
                    $oldStock,
                    $newStock,
                    'order_created',
                    "Order #{$event->getOrderNumber()}",
                    $event->getUserId()
                );

                // 检查库存状态
                if ($newStock <= 0) {
                    $outOfStockProducts[] = $product->sku;
                } elseif ($newStock <= 10) {
                    $lowStockProducts[] = $product->sku;
                }

                $this->log($event, 'Stock updated for product', [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'quantity_ordered' => $item['quantity']
                ]);
            }

            // 处理低库存和缺货通知
            if (!empty($outOfStockProducts)) {
                $this->handleOutOfStock($outOfStockProducts, $event);
            }

            if (!empty($lowStockProducts)) {
                $this->handleLowStock($lowStockProducts, $event);
            }
        });
    }

    protected function handleOrderCancelled(OrderCancelledEvent $event): void
    {
        DB::transaction(function () use ($event) {
            foreach ($event->getItems() as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    $this->logError($event, "Product not found: {$item['product_id']}");
                    continue;
                }

                $oldStock = $product->stock_quantity;
                $newStock = $oldStock + $item['quantity'];

                // 恢复库存
                $product->stock_quantity = $newStock;
                $product->save();

                // 记录库存变化
                $this->recordInventoryChange(
                    $product->id,
                    $oldStock,
                    $newStock,
                    'order_cancelled',
                    "Order #{$event->getOrderNumber()} cancelled",
                    $event->getCancelledBy()
                );

                $this->log($event, 'Stock restored for product', [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'quantity_restored' => $item['quantity']
                ]);
            }
        });
    }

    protected function handleProductUpdated(ProductUpdatedEvent $event): void
    {
        if (!$event->hasStockChanged()) {
            return;
        }

        $product = Product::find($event->getProductId());
        if (!$product) {
            $this->logError($event, "Product not found: {$event->getProductId()}");
            return;
        }

        $oldStock = $event->getPreviousStock();
        $newStock = $event->getCurrentStock();
        $stockChange = $event->getStockChange();

        // 记录库存变化
        $this->recordInventoryChange(
            $product->id,
            $oldStock,
            $newStock,
            'manual_update',
            'Manual stock update',
            $event->getUpdatedBy()
        );

        // 检查库存状态并处理相应通知
        if ($stockChange > 0) {
            // 库存增加，检查是否从缺货状态恢复
            if ($oldStock <= 0 && $newStock > 0) {
                $this->handleStockRestored($product, $event);
            }
        } else {
            // 库存减少，检查是否进入低库存或缺货状态
            if ($newStock <= 0) {
                $this->handleOutOfStock([$product->sku], $event);
            } elseif ($newStock <= 10 && $oldStock > 10) {
                $this->handleLowStock([$product->sku], $event);
            }
        }

        $this->log($event, 'Manual stock update recorded', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'stock_change' => $stockChange
        ]);
    }

    /**
     * 记录库存变化
     */
    protected function recordInventoryChange(
        int $productId,
        int $oldStock,
        int $newStock,
        string $changeType,
        string $reason,
        string $changedBy
    ): void {
        DB::table('inventory_changes')->insert([
            'product_id' => $productId,
            'old_quantity' => $oldStock,
            'new_quantity' => $newStock,
            'change_type' => $changeType,
            'reason' => $reason,
            'changed_by' => $changedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * 处理缺货情况
     */
    protected function handleOutOfStock(array $productSkus, EventInterface $event): void
    {
        $message = "以下产品已缺货: " . implode(', ', $productSkus);
        
        Log::warning($message, [
            'event_id' => $event->getId(),
            'products' => $productSkus
        ]);

        // 发送缺货通知给采购部门
        $this->sendStockAlert('out_of_stock', $productSkus, $event);
        
        // 更新产品状态为缺货
        $this->updateProductStatus($productSkus, 'out_of_stock');
    }

    /**
     * 处理低库存情况
     */
    protected function handleLowStock(array $productSkus, EventInterface $event): void
    {
        $message = "以下产品库存不足: " . implode(', ', $productSkus);
        
        Log::warning($message, [
            'event_id' => $event->getId(),
            'products' => $productSkus
        ]);

        // 发送低库存通知给采购部门
        $this->sendStockAlert('low_stock', $productSkus, $event);
    }

    /**
     * 处理库存恢复
     */
    protected function handleStockRestored(Product $product, EventInterface $event): void
    {
        Log::info("Product {$product->sku} stock restored", [
            'event_id' => $event->getId(),
            'product_id' => $product->id,
            'new_stock' => $product->stock_quantity
        ]);

        // 更新产品状态为可用
        $product->status = 'available';
        $product->save();

        // 发送库存恢复通知
        $this->sendStockAlert('stock_restored', [$product->sku], $event);
    }

    /**
     * 发送库存警报
     */
    protected function sendStockAlert(string $alertType, array $productSkus, EventInterface $event): void
    {
        // 这里可以集成邮件、短信或其他通知方式
        // 目前只记录日志
        Log::info("Stock alert sent: {$alertType}", [
            'alert_type' => $alertType,
            'products' => $productSkus,
            'event_id' => $event->getId()
        ]);
    }

    /**
     * 更新产品状态
     */
    protected function updateProductStatus(array $productSkus, string $status): void
    {
        Product::whereIn('sku', $productSkus)->update(['status' => $status]);
    }

    /**
     * 获取库存报告
     */
    public function getInventoryReport(): array
    {
        $totalProducts = Product::count();
        $inStockProducts = Product::where('stock_quantity', '>', 0)->count();
        $lowStockProducts = Product::where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', 10)->count();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();

        $recentChanges = DB::table('inventory_changes')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'in_stock' => $inStockProducts,
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
                'stock_rate' => $totalProducts > 0 ? round(($inStockProducts / $totalProducts) * 100, 2) : 0,
            ],
            'recent_changes' => $recentChanges,
            'alerts' => [
                'out_of_stock_count' => $outOfStockProducts,
                'low_stock_count' => $lowStockProducts,
            ]
        ];
    }
}