<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * 实时事件服务
 * 
 * 处理业务事件的实时推送，包括订单状态、库存变化、系统消息等
 */
class RealtimeEventService
{
    public function __construct(
        private WebSocketService $webSocketService,
        private CacheService $cacheService
    ) {}

    /**
     * 订单状态变更通知
     */
    public function notifyOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        $message = [
            'type' => 'order_status_changed',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'status_label' => $this->getStatusLabel($newStatus),
                'timestamp' => now()->toISOString(),
                'amount' => $order->total_amount,
                'currency' => $order->currency ?? 'CNY'
            ]
        ];

        // 发送给订单创建者
        $this->webSocketService->sendToUser($order->user_id, $message);

        // 如果订单状态变为重要状态，广播到管理频道
        if (in_array($newStatus, ['confirmed', 'shipped', 'delivered', 'cancelled'])) {
            $this->webSocketService->broadcastToChannel('admin_orders', [
                'type' => 'order_status_update',
                'data' => array_merge($message['data'], [
                    'user_id' => $order->user_id,
                    'customer_name' => $order->user->name ?? 'Unknown'
                ])
            ]);
        }

        Log::info('Order status change notification sent', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    /**
     * 新订单创建通知
     */
    public function notifyNewOrder(Order $order): void
    {
        $message = [
            'type' => 'new_order',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $order->user_id,
                'customer_name' => $order->user->name ?? 'Unknown',
                'total_amount' => $order->total_amount,
                'currency' => $order->currency ?? 'CNY',
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->toISOString(),
                'priority' => $order->total_amount > 10000 ? 'high' : 'normal'
            ]
        ];

        // 通知用户
        $this->webSocketService->sendToUser($order->user_id, [
            'type' => 'order_created',
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => '您的订单已成功创建',
                'total_amount' => $order->total_amount,
                'currency' => $order->currency ?? 'CNY'
            ]
        ]);

        // 广播到管理员频道
        $this->webSocketService->broadcastToChannel('admin_orders', $message);

        // 如果是高价值订单，发送紧急通知
        if ($order->total_amount > 10000) {
            $this->webSocketService->broadcastToChannel('admin_alerts', [
                'type' => 'high_value_order',
                'data' => array_merge($message['data'], [
                    'alert_level' => 'high',
                    'message' => '高价值订单需要关注'
                ])
            ]);
        }

        Log::info('New order notification sent', [
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'amount' => $order->total_amount
        ]);
    }

    /**
     * 库存变化通知
     */
    public function notifyInventoryChange(Product $product, int $oldStock, int $newStock, string $reason = 'sale'): void
    {
        $stockDifference = $newStock - $oldStock;
        
        $message = [
            'type' => 'inventory_changed',
            'data' => [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'old_stock' => $oldStock,
                'new_stock' => $newStock,
                'difference' => $stockDifference,
                'reason' => $reason,
                'timestamp' => now()->toISOString(),
                'price' => $product->price,
                'currency' => $product->currency ?? 'CNY'
            ]
        ];

        // 广播到库存管理频道
        $this->webSocketService->broadcastToChannel('inventory', $message);

        // 如果库存过低，发送预警
        if ($newStock <= $product->low_stock_threshold && $stockDifference < 0) {
            $this->sendLowStockAlert($product, $newStock);
        }

        // 如果库存售罄，发送紧急通知
        if ($newStock === 0 && $oldStock > 0) {
            $this->sendOutOfStockAlert($product);
        }

        Log::info('Inventory change notification sent', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'old_stock' => $oldStock,
            'new_stock' => $newStock
        ]);
    }

    /**
     * 低库存预警
     */
    private function sendLowStockAlert(Product $product, int $currentStock): void
    {
        $alert = [
            'type' => 'low_stock_alert',
            'data' => [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'current_stock' => $currentStock,
                'threshold' => $product->low_stock_threshold,
                'recommended_reorder' => $product->recommended_reorder_quantity ?? 100,
                'price' => $product->price,
                'currency' => $product->currency ?? 'CNY',
                'urgency' => $currentStock <= 10 ? 'critical' : 'warning',
                'timestamp' => now()->toISOString()
            ]
        ];

        $this->webSocketService->broadcastToChannel('inventory_alerts', $alert);
        $this->webSocketService->broadcastToChannel('admin_alerts', $alert);

        Log::warning('Low stock alert sent', [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'current_stock' => $currentStock
        ]);
    }

    /**
     * 缺货紧急通知
     */
    private function sendOutOfStockAlert(Product $product): void
    {
        $alert = [
            'type' => 'out_of_stock_alert',
            'data' => [
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'timestamp' => now()->toISOString(),
                'price' => $product->price,
                'currency' => $product->currency ?? 'CNY',
                'urgency' => 'critical',
                'message' => '产品已售罄，需要立即补货'
            ]
        ];

        $this->webSocketService->broadcastToChannel('inventory_alerts', $alert);
        $this->webSocketService->broadcastToChannel('admin_alerts', $alert);
        $this->webSocketService->broadcastToChannel('sales_alerts', $alert);

        Log::error('Out of stock alert sent', [
            'product_id' => $product->id,
            'sku' => $product->sku
        ]);
    }

    /**
     * 询价状态变更通知
     */
    public function notifyInquiryStatusChange(Inquiry $inquiry, string $oldStatus, string $newStatus): void
    {
        $message = [
            'type' => 'inquiry_status_changed',
            'data' => [
                'inquiry_id' => $inquiry->id,
                'inquiry_number' => $inquiry->inquiry_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'status_label' => $this->getInquiryStatusLabel($newStatus),
                'timestamp' => now()->toISOString(),
                'product_name' => $inquiry->product_name ?? 'N/A',
                'quantity' => $inquiry->quantity
            ]
        ];

        // 通知询价创建者
        $this->webSocketService->sendToUser($inquiry->user_id, $message);

        // 如果状态变为已报价，通知销售团队
        if ($newStatus === 'quoted') {
            $this->webSocketService->broadcastToChannel('sales_inquiries', [
                'type' => 'new_quote',
                'data' => array_merge($message['data'], [
                    'user_id' => $inquiry->user_id,
                    'customer_name' => $inquiry->user->name ?? 'Unknown',
                    'quoted_price' => $inquiry->quoted_price,
                    'valid_until' => $inquiry->valid_until?->toISOString()
                ])
            ]);
        }

        Log::info('Inquiry status change notification sent', [
            'inquiry_id' => $inquiry->id,
            'user_id' => $inquiry->user_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    /**
     * 系统消息广播
     */
    public function broadcastSystemMessage(string $title, string $message, string $type = 'info', ?array $targetUsers = null): void
    {
        $systemMessage = [
            'type' => 'system_message',
            'data' => [
                'title' => $title,
                'message' => $message,
                'message_type' => $type, // info, warning, error, maintenance
                'timestamp' => now()->toISOString(),
                'priority' => $this->getMessagePriority($type),
                'id' => uniqid('sys_', true)
            ]
        ];

        if ($targetUsers) {
            // 发送给特定用户
            foreach ($targetUsers as $userId) {
                $this->webSocketService->sendToUser($userId, $systemMessage);
            }
        } else {
            // 广播给所有用户
            $this->webSocketService->broadcastToChannel('system_announcements', $systemMessage);
        }

        // 重要消息同时发送到管理员频道
        if (in_array($type, ['warning', 'error', 'maintenance'])) {
            $this->webSocketService->broadcastToChannel('admin_system', $systemMessage);
        }

        Log::info('System message broadcasted', [
            'title' => $title,
            'type' => $type,
            'target_users' => $targetUsers ? count($targetUsers) : 'all'
        ]);
    }

    /**
     * 维护通知
     */
    public function sendMaintenanceNotification(string $startTime, string $endTime, string $description = ''): void
    {
        $maintenanceMessage = [
            'type' => 'maintenance_notification',
            'data' => [
                'title' => '系统维护通知',
                'message' => "系统将于 {$startTime} 至 {$endTime} 进行维护",
                'description' => $description,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $this->calculateDuration($startTime, $endTime),
                'timestamp' => now()->toISOString(),
                'priority' => 'high'
            ]
        ];

        $this->webSocketService->broadcastToChannel('system_announcements', $maintenanceMessage);
        $this->webSocketService->broadcastToChannel('admin_system', $maintenanceMessage);

        Log::info('Maintenance notification sent', [
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
    }

    /**
     * 新功能发布通知
     */
    public function announceNewFeature(string $featureName, string $description, array $featureHighlights = []): void
    {
        $featureMessage = [
            'type' => 'new_feature_announcement',
            'data' => [
                'title' => '新功能发布',
                'feature_name' => $featureName,
                'description' => $description,
                'highlights' => $featureHighlights,
                'version' => $this->getCurrentVersion(),
                'timestamp' => now()->toISOString(),
                'priority' => 'normal'
            ]
        ];

        $this->webSocketService->broadcastToChannel('feature_announcements', $featureMessage);

        Log::info('New feature announcement sent', [
            'feature_name' => $featureName
        ]);
    }

    /**
     * 客服聊天消息
     */
    public function sendChatMessage(int $fromUserId, int $toUserId, string $message, string $chatType = 'customer_service'): void
    {
        $chatMessage = [
            'type' => 'chat_message',
            'data' => [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'message' => $message,
                'chat_type' => $chatType,
                'timestamp' => now()->toISOString(),
                'message_id' => uniqid('chat_', true)
            ]
        ];

        // 发送给接收者
        $this->webSocketService->sendToUser($toUserId, $chatMessage);

        // 如果是客服聊天，同时发送到客服频道
        if ($chatType === 'customer_service') {
            $this->webSocketService->broadcastToChannel('customer_service', [
                'type' => 'customer_chat',
                'data' => array_merge($chatMessage['data'], [
                    'from_user_name' => User::find($fromUserId)?->name ?? 'Unknown',
                    'to_user_name' => User::find($toUserId)?->name ?? 'Unknown'
                ])
            ]);
        }

        // 存储聊天记录
        $this->storeChatMessage($fromUserId, $toUserId, $message, $chatType);

        Log::info('Chat message sent', [
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'chat_type' => $chatType
        ]);
    }

    /**
     * 实时统计更新
     */
    public function updateRealtimeStats(): void
    {
        $stats = $this->getRealtimeStats();

        $statsMessage = [
            'type' => 'stats_update',
            'data' => [
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]
        ];

        // 发送给管理员
        $this->webSocketService->broadcastToChannel('admin_stats', $statsMessage);

        // 缓存统计数据
        $this->cacheService->set('realtime_stats', $stats, 300); // 5分钟缓存

        Log::debug('Realtime stats updated', $stats);
    }

    /**
     * 获取实时统计数据
     */
    private function getRealtimeStats(): array
    {
        return [
            'orders' => [
                'today' => DB::table('orders')->whereDate('created_at', today())->count(),
                'this_week' => DB::table('orders')->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->count(),
                'this_month' => DB::table('orders')->whereMonth('created_at', now()->month)->count(),
                'pending' => DB::table('orders')->where('status', 'pending')->count(),
                'total_amount_today' => DB::table('orders')->whereDate('created_at', today())->sum('total_amount')
            ],
            'inquiries' => [
                'pending' => DB::table('inquiries')->where('status', 'pending')->count(),
                'quoted' => DB::table('inquiries')->where('status', 'quoted')->count(),
                'today' => DB::table('inquiries')->whereDate('created_at', today())->count()
            ],
            'products' => [
                'low_stock' => DB::table('products')
                    ->whereColumn('stock', '<=', 'low_stock_threshold')
                    ->where('stock', '>', 0)
                    ->count(),
                'out_of_stock' => DB::table('products')->where('stock', 0)->count(),
                'total' => DB::table('products')->count()
            ],
            'users' => [
                'online' => count($this->webSocketService->getAuthenticatedUsers()),
                'new_today' => DB::table('users')->whereDate('created_at', today())->count(),
                'total' => DB::table('users')->count()
            ]
        ];
    }

    /**
     * 存储聊天消息
     */
    private function storeChatMessage(int $fromUserId, int $toUserId, string $message, string $chatType): void
    {
        // 这里应该存储到数据库，暂时只记录日志
        Log::info('Chat message stored', [
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'chat_type' => $chatType,
            'message_length' => strlen($message)
        ]);
    }

    /**
     * 获取状态标签
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => '待处理',
            'confirmed' => '已确认',
            'processing' => '处理中',
            'shipped' => '已发货',
            'delivered' => '已送达',
            'cancelled' => '已取消'
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * 获取询价状态标签
     */
    private function getInquiryStatusLabel(string $status): string
    {
        $labels = [
            'pending' => '待处理',
            'quoted' => '已报价',
            'accepted' => '已接受',
            'rejected' => '已拒绝',
            'expired' => '已过期'
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * 获取消息优先级
     */
    private function getMessagePriority(string $type): string
    {
        $priorities = [
            'info' => 'normal',
            'warning' => 'medium',
            'error' => 'high',
            'maintenance' => 'high'
        ];

        return $priorities[$type] ?? 'normal';
    }

    /**
     * 计算持续时间
     */
    private function calculateDuration(string $startTime, string $endTime): string
    {
        $start = new \DateTime($startTime);
        $end = new \DateTime($endTime);
        $interval = $start->diff($end);

        return $interval->format('%h小时%i分钟');
    }

    /**
     * 获取当前版本
     */
    private function getCurrentVersion(): string
    {
        return '2.0.0'; // 应该从配置或版本文件读取
    }
}