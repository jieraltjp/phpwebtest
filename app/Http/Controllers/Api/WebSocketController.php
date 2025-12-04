<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WebSocketService;
use App\Services\RealtimeEventService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * WebSocket API控制器
 * 
 * 提供WebSocket连接的HTTP API接口，用于认证、频道管理等
 */
class WebSocketController extends Controller
{
    public function __construct(
        private WebSocketService $webSocketService,
        private RealtimeEventService $realtimeEventService
    ) {}

    /**
     * 获取WebSocket连接配置
     */
    public function getConfig(Request $request): JsonResponse
    {
        $config = [
            'websocket_url' => config('app.url') . ':8080',
            'protocols' => ['websocket'],
            'auth_endpoint' => route('api.websocket.auth'),
            'channels' => [
                'public' => [
                    'system_announcements',
                    'feature_announcements'
                ],
                'private' => [
                    'user.{userId}',
                    'admin_orders',
                    'admin_stats',
                    'inventory',
                    'inventory_alerts',
                    'admin_alerts',
                    'sales_alerts',
                    'sales_inquiries',
                    'customer_service',
                    'admin_system'
                ]
            ],
            'features' => [
                'authentication' => true,
                'private_channels' => true,
                'presence_channels' => true,
                'client_events' => true,
                'message_history' => true,
                'rate_limiting' => true
            ],
            'rate_limits' => [
                'messages_per_second' => 100,
                'connections_per_ip' => 10,
                'max_connections' => 5000
            ],
            'heartbeat' => [
                'interval' => 30,
                'timeout' => 60
            ]
        ];

        return ApiResponseService::success($config, 'WebSocket configuration retrieved');
    }

    /**
     * 生成WebSocket认证令牌
     */
    public function authenticate(Request $request): JsonResponse
    {
        $request->validate([
            'connection_id' => 'required|string',
            'channel_name' => 'nullable|string'
        ]);

        try {
            $user = $request->user();
            $connectionId = $request->input('connection_id');
            $channelName = $request->input('channel_name');

            if (!$user) {
                return ApiResponseService::error('User not authenticated', [], 401);
            }

            // 生成认证令牌
            $authToken = $this->generateAuthToken($user, $connectionId, $channelName);

            return ApiResponseService::success([
                'auth_token' => $authToken,
                'user_id' => $user->id,
                'connection_id' => $connectionId,
                'expires_at' => now()->addMinutes(60)->toISOString()
            ], 'WebSocket authentication token generated');

        } catch (\Exception $e) {
            Log::error('WebSocket authentication error', [
                'error' => $e->getMessage(),
                'connection_id' => $request->input('connection_id')
            ]);

            return ApiResponseService::error('Authentication failed', [], 500);
        }
    }

    /**
     * 发送系统消息
     */
    public function sendSystemMessage(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'type' => 'required|in:info,warning,error,maintenance',
            'target_users' => 'nullable|array',
            'target_users.*' => 'integer'
        ]);

        try {
            $title = $request->input('title');
            $message = $request->input('message');
            $type = $request->input('type');
            $targetUsers = $request->input('target_users');

            $this->realtimeEventService->broadcastSystemMessage($title, $message, $type, $targetUsers);

            return ApiResponseService::success([], 'System message broadcasted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to send system message', [
                'error' => $e->getMessage(),
                'title' => $request->input('title')
            ]);

            return ApiResponseService::error('Failed to send system message', [], 500);
        }
    }

    /**
     * 发送维护通知
     */
    public function sendMaintenanceNotification(Request $request): JsonResponse
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
            $description = $request->input('description', '');

            $this->realtimeEventService->sendMaintenanceNotification($startTime, $endTime, $description);

            return ApiResponseService::success([], 'Maintenance notification sent successfully');

        } catch (\Exception $e) {
            Log::error('Failed to send maintenance notification', [
                'error' => $e->getMessage(),
                'start_time' => $request->input('start_time')
            ]);

            return ApiResponseService::error('Failed to send maintenance notification', [], 500);
        }
    }

    /**
     * 发送聊天消息
     */
    public function sendChatMessage(Request $request): JsonResponse
    {
        $request->validate([
            'to_user_id' => 'required|integer|exists:users,id',
            'message' => 'required|string|max:1000',
            'chat_type' => 'required|in:customer_service,direct'
        ]);

        try {
            $fromUserId = $request->user()->id;
            $toUserId = $request->input('to_user_id');
            $message = $request->input('message');
            $chatType = $request->input('chat_type');

            $this->realtimeEventService->sendChatMessage($fromUserId, $toUserId, $message, $chatType);

            return ApiResponseService::success([], 'Chat message sent successfully');

        } catch (\Exception $e) {
            Log::error('Failed to send chat message', [
                'error' => $e->getMessage(),
                'from_user_id' => $request->user()->id,
                'to_user_id' => $request->input('to_user_id')
            ]);

            return ApiResponseService::error('Failed to send chat message', [], 500);
        }
    }

    /**
     * 获取WebSocket统计信息
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $stats = $this->webSocketService->getStats();

            // 添加额外的统计信息
            $stats['redis_info'] = [
                'connected_clients' => Redis::info('clients')['connected_clients'] ?? 0,
                'used_memory' => Redis::info('memory')['used_memory_human'] ?? 'N/A',
                'total_commands_processed' => Redis::info('stats')['total_commands_processed'] ?? 0
            ];

            // 添加队列统计
            $stats['queues'] = [
                'outgoing_messages' => Redis::llen('websocket_outgoing'),
                'offline_messages' => $this->getTotalOfflineMessages()
            ];

            return ApiResponseService::success($stats, 'WebSocket statistics retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get WebSocket stats', [
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve WebSocket statistics', [], 500);
        }
    }

    /**
     * 强制断开连接
     */
    public function disconnectConnection(Request $request): JsonResponse
    {
        $request->validate([
            'connection_id' => 'required|string',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            $connectionId = $request->input('connection_id');
            $reason = $request->input('reason', 'Disconnected by administrator');

            $this->webSocketService->disconnect($connectionId);

            Log::info('Connection forcibly disconnected', [
                'connection_id' => $connectionId,
                'reason' => $reason,
                'admin_id' => $request->user()->id
            ]);

            return ApiResponseService::success([], 'Connection disconnected successfully');

        } catch (\Exception $e) {
            Log::error('Failed to disconnect connection', [
                'error' => $e->getMessage(),
                'connection_id' => $request->input('connection_id')
            ]);

            return ApiResponseService::error('Failed to disconnect connection', [], 500);
        }
    }

    /**
     * 广播测试消息
     */
    public function broadcastTest(Request $request): JsonResponse
    {
        $request->validate([
            'channel' => 'required|string',
            'message' => 'required|string|max:500'
        ]);

        try {
            $channel = $request->input('channel');
            $message = $request->input('message');

            $testMessage = [
                'type' => 'test_message',
                'data' => [
                    'message' => $message,
                    'timestamp' => now()->toISOString(),
                    'sent_by' => $request->user()->name,
                    'test' => true
                ]
            ];

            $sentCount = $this->webSocketService->broadcastToChannel($channel, $testMessage);

            return ApiResponseService::success([
                'channel' => $channel,
                'sent_count' => $sentCount,
                'message' => $message
            ], 'Test message broadcasted successfully');

        } catch (\Exception $e) {
            Log::error('Failed to broadcast test message', [
                'error' => $e->getMessage(),
                'channel' => $request->input('channel')
            ]);

            return ApiResponseService::error('Failed to broadcast test message', [], 500);
        }
    }

    /**
     * 获取在线用户列表
     */
    public function getOnlineUsers(Request $request): JsonResponse
    {
        try {
            $stats = $this->webSocketService->getStats();
            $onlineUserIds = array_keys($this->webSocketService->getAuthenticatedUsers() ?? []);

            $onlineUsers = [];
            if (!empty($onlineUserIds)) {
                $onlineUsers = \App\Models\User::whereIn('id', $onlineUserIds)
                    ->select(['id', 'name', 'email', 'last_login_at'])
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'last_login_at' => $user->last_login_at?->toISOString(),
                            'status' => 'online'
                        ];
                    });
            }

            return ApiResponseService::success([
                'online_users' => $onlineUsers,
                'total_online' => count($onlineUsers),
                'authenticated_connections' => $stats['authenticated_connections'],
                'anonymous_connections' => $stats['anonymous_connections']
            ], 'Online users retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get online users', [
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve online users', [], 500);
        }
    }

    /**
     * 清理过期连接
     */
    public function cleanupConnections(Request $request): JsonResponse
    {
        try {
            $cleanedCount = $this->webSocketService->cleanupExpiredConnections();

            return ApiResponseService::success([
                'cleaned_connections' => $cleanedCount
            ], 'Expired connections cleaned successfully');

        } catch (\Exception $e) {
            Log::error('Failed to cleanup connections', [
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to cleanup connections', [], 500);
        }
    }

    /**
     * 获取历史消息
     */
    public function getMessageHistory(Request $request): JsonResponse
    {
        $request->validate([
            'channel' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:100',
            'before' => 'nullable|date'
        ]);

        try {
            $channel = $request->input('channel');
            $limit = $request->input('limit', 50);
            $before = $request->input('before');

            // 这里应该从数据库获取历史消息
            // 暂时返回模拟数据
            $messages = [
                [
                    'id' => 'msg_001',
                    'type' => 'system_message',
                    'data' => [
                        'title' => 'Welcome',
                        'message' => 'Welcome to the WebSocket service',
                        'timestamp' => now()->subMinutes(30)->toISOString()
                    ]
                ]
            ];

            return ApiResponseService::success([
                'messages' => $messages,
                'channel' => $channel,
                'total' => count($messages)
            ], 'Message history retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get message history', [
                'error' => $e->getMessage(),
                'channel' => $request->input('channel')
            ]);

            return ApiResponseService::error('Failed to retrieve message history', [], 500);
        }
    }

    /**
     * 生成认证令牌
     */
    private function generateAuthToken($user, string $connectionId, ?string $channelName): string
    {
        $payload = [
            'user_id' => $user->id,
            'connection_id' => $connectionId,
            'channel_name' => $channelName,
            'iat' => time(),
            'exp' => time() + 3600, // 1小时过期
            'jti' => uniqid('ws_auth_', true)
        ];

        return app('tymon.jwt.auth')->fromUser($user, $payload);
    }

    /**
     * 获取离线消息总数
     */
    private function getTotalOfflineMessages(): int
    {
        $keys = Redis::keys('offline_messages:*');
        $total = 0;

        foreach ($keys as $key) {
            $total += Redis::llen($key);
        }

        return $total;
    }
}