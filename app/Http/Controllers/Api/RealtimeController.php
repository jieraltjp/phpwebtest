<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MessagePersistenceService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * 实时通信API控制器
 * 
 * 提供通知管理、聊天历史、实时统计等功能
 */
class RealtimeController extends Controller
{
    public function __construct(
        private MessagePersistenceService $messagePersistenceService
    ) {}

    /**
     * 获取用户通知列表
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $limit = $request->get('limit', 20);
            $before = $request->get('before');

            // 获取离线消息
            $offlineMessages = $this->messagePersistenceService->getOfflineMessages($user->id, $limit);

            // 获取未读统计
            $unreadStats = $this->messagePersistenceService->getUnreadMessageStats($user->id);

            // 格式化通知数据
            $notifications = collect($offlineMessages)->map(function ($msg) {
                $message = $msg['message'];
                return [
                    'id' => $msg['id'],
                    'type' => $message['type'],
                    'title' => $message['data']['title'] ?? $this->getDefaultTitle($message['type']),
                    'message' => $message['data']['message'] ?? $this->getDefaultMessage($message),
                    'data' => $message['data'],
                    'created_at' => $msg['created_at'],
                    'read' => false,
                    'priority' => $this->getNotificationPriority($message)
                ];
            });

            return ApiResponseService::success([
                'notifications' => $notifications,
                'unread_stats' => $unreadStats,
                'has_more' => count($offlineMessages) >= $limit
            ], 'Notifications retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Failed to get notifications', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve notifications', [], 500);
        }
    }

    /**
     * 标记通知为已读
     */
    public function markNotificationsAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'string'
        ]);

        try {
            $user = $request->user();
            $notificationIds = $request->get('notification_ids');
            $markedCount = 0;

            foreach ($notificationIds as $notificationId) {
                if ($this->messagePersistenceService->markMessageAsRead($user->id, $notificationId)) {
                    $markedCount++;
                }
            }

            return ApiResponseService::success([
                'marked_count' => $markedCount
            ], 'Notifications marked as read');

        } catch (\Exception $e) {
            Log::error('Failed to mark notifications as read', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to mark notifications as read', [], 500);
        }
    }

    /**
     * 清除所有通知
     */
    public function clearNotifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cleared = $this->messagePersistenceService->clearOfflineMessages($user->id);

            return ApiResponseService::success([
                'cleared_count' => $cleared
            ], 'All notifications cleared');

        } catch (\Exception $e) {
            Log::error('Failed to clear notifications', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to clear notifications', [], 500);
        }
    }

    /**
     * 获取聊天历史
     */
    public function getChatHistory(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:100',
            'before' => 'nullable|date'
        ]);

        try {
            $currentUserId = $request->user()->id;
            $otherUserId = $request->get('user_id');
            $limit = $request->get('limit', 50);
            $before = $request->get('before');

            // 获取聊天历史
            $messages = $this->messagePersistenceService->getChatHistory(
                $currentUserId,
                $otherUserId,
                $limit,
                $before
            );

            // 标记消息为已读
            $this->messagePersistenceService->markChatMessagesAsRead($currentUserId, $otherUserId);

            return ApiResponseService::success([
                'messages' => $messages,
                'other_user' => $this->getUserInfo($otherUserId),
                'has_more' => count($messages) >= $limit
            ], 'Chat history retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get chat history', [
                'user_id' => $request->user()->id,
                'other_user_id' => $request->get('user_id'),
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve chat history', [], 500);
        }
    }

    /**
     * 标记聊天消息为已读
     */
    public function markChatAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        try {
            $currentUserId = $request->user()->id;
            $otherUserId = $request->get('user_id');

            $markedCount = $this->messagePersistenceService->markChatMessagesAsRead(
                $currentUserId,
                $otherUserId
            );

            return ApiResponseService::success([
                'marked_count' => $markedCount
            ], 'Chat messages marked as read');

        } catch (\Exception $e) {
            Log::error('Failed to mark chat as read', [
                'user_id' => $request->user()->id,
                'other_user_id' => $request->get('user_id'),
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to mark chat as read', [], 500);
        }
    }

    /**
     * 获取实时统计数据
     */
    public function getRealtimeStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // 获取用户相关的实时统计
            $userStats = $this->getUserRealtimeStats($user);

            // 获取系统统计（如果是管理员）
            $systemStats = [];
            if ($user->role === 'admin') {
                $systemStats = $this->getSystemRealtimeStats();
            }

            return ApiResponseService::success([
                'user_stats' => $userStats,
                'system_stats' => $systemStats,
                'timestamp' => now()->toISOString()
            ], 'Realtime stats retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get realtime stats', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve realtime stats', [], 500);
        }
    }

    /**
     * 获取用户在线状态
     */
    public function getOnlineStatus(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer'
        ]);

        try {
            $userIds = $request->get('user_ids');
            $onlineStatus = [];

            foreach ($userIds as $userId) {
                $unreadStats = $this->messagePersistenceService->getUnreadMessageStats($userId);
                $onlineStatus[$userId] = [
                    'online' => $this->isUserOnline($userId),
                    'last_seen' => $this->getUserLastSeen($userId),
                    'unread_count' => $unreadStats['total_unread']
                ];
            }

            return ApiResponseService::success([
                'online_status' => $onlineStatus
            ], 'Online status retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get online status', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve online status', [], 500);
        }
    }

    /**
     * 搜索聊天消息
     */
    public function searchChatMessages(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:1|max:100',
            'user_id' => 'nullable|integer|exists:users,id',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        try {
            $currentUserId = $request->user()->id;
            $query = $request->get('query');
            $otherUserId = $request->get('user_id');
            $limit = $request->get('limit', 20);

            $messages = DB::table('chat_messages')
                ->where(function ($q) use ($currentUserId, $otherUserId) {
                    $q->where('from_user_id', $currentUserId);
                    if ($otherUserId) {
                        $q->where('to_user_id', $otherUserId);
                    } else {
                        $q->where('to_user_id', $currentUserId);
                    }
                })
                ->orWhere(function ($q) use ($currentUserId, $otherUserId) {
                    $q->where('to_user_id', $currentUserId);
                    if ($otherUserId) {
                        $q->where('from_user_id', $otherUserId);
                    } else {
                        $q->where('from_user_id', $currentUserId);
                    }
                })
                ->where('message', 'LIKE', "%{$query}%")
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $results = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'from_user_id' => $message->from_user_id,
                    'to_user_id' => $message->to_user_id,
                    'message' => $message->message,
                    'chat_type' => $message->chat_type,
                    'created_at' => $message->created_at,
                    'highlight' => $this->highlightSearchTerm($message->message, $query)
                ];
            });

            return ApiResponseService::success([
                'results' => $results,
                'query' => $query,
                'total' => count($results)
            ], 'Chat messages searched');

        } catch (\Exception $e) {
            Log::error('Failed to search chat messages', [
                'user_id' => $request->user()->id,
                'query' => $request->get('query'),
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to search chat messages', [], 500);
        }
    }

    /**
     * 获取通知设置
     */
    public function getNotificationSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $settings = $this->getUserNotificationSettings($user);

            return ApiResponseService::success([
                'settings' => $settings
            ], 'Notification settings retrieved');

        } catch (\Exception $e) {
            Log::error('Failed to get notification settings', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to retrieve notification settings', [], 500);
        }
    }

    /**
     * 更新通知设置
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.email_notifications' => 'boolean',
            'settings.push_notifications' => 'boolean',
            'settings.sound_enabled' => 'boolean',
            'settings.desktop_notifications' => 'boolean',
            'settings.order_updates' => 'boolean',
            'settings.chat_messages' => 'boolean',
            'settings.system_announcements' => 'boolean'
        ]);

        try {
            $user = $request->user();
            $settings = $request->get('settings');

            $this->saveUserNotificationSettings($user, $settings);

            return ApiResponseService::success([], 'Notification settings updated');

        } catch (\Exception $e) {
            Log::error('Failed to update notification settings', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return ApiResponseService::error('Failed to update notification settings', [], 500);
        }
    }

    // 私有辅助方法

    /**
     * 获取默认通知标题
     */
    private function getDefaultTitle(string $type): string
    {
        $titles = [
            'order_status_changed' => '订单状态更新',
            'new_order' => '新订单',
            'inventory_changed' => '库存变化',
            'system_message' => '系统通知',
            'chat_message' => '新消息',
            'inquiry_status_changed' => '询价状态更新',
            'maintenance_notification' => '维护通知'
        ];

        return $titles[$type] ?? '通知';
    }

    /**
     * 获取默认通知消息
     */
    private function getDefaultMessage(array $message): string
    {
        if (isset($message['data']['message'])) {
            return $message['data']['message'];
        }

        return '您有一条新通知';
    }

    /**
     * 获取通知优先级
     */
    private function getNotificationPriority(array $message): string
    {
        $type = $message['type'] ?? 'info';
        
        $highPriorityTypes = ['error', 'maintenance', 'out_of_stock_alert'];
        $mediumPriorityTypes = ['warning', 'order_status_changed', 'chat_message'];

        if (in_array($type, $highPriorityTypes)) {
            return 'high';
        } elseif (in_array($type, $mediumPriorityTypes)) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * 获取用户信息
     */
    private function getUserInfo(int $userId): ?array
    {
        $user = DB::table('users')->find($userId);
        
        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar ?? null,
            'online' => $this->isUserOnline($userId)
        ];
    }

    /**
     * 获取用户实时统计
     */
    private function getUserRealtimeStats($user): array
    {
        $unreadStats = $this->messagePersistenceService->getUnreadMessageStats($user->id);

        return [
            'unread_notifications' => $unreadStats['offline_messages'],
            'unread_chat_messages' => $unreadStats['unread_chat_messages'],
            'total_unread' => $unreadStats['total_unread'],
            'recent_orders' => DB::table('orders')
                ->where('user_id', $user->id)
                ->where('created_at', '>', now()->subDays(7))
                ->count(),
            'recent_inquiries' => DB::table('inquiries')
                ->where('user_id', $user->id)
                ->where('created_at', '>', now()->subDays(7))
                ->count()
        ];
    }

    /**
     * 获取系统实时统计
     */
    private function getSystemRealtimeStats(): array
    {
        return $this->messagePersistenceService->getMessageStats();
    }

    /**
     * 检查用户是否在线
     */
    private function isUserOnline(int $userId): bool
    {
        // 这里应该检查WebSocket连接状态
        // 暂时返回false
        return false;
    }

    /**
     * 获取用户最后在线时间
     */
    private function getUserLastSeen(int $userId): ?string
    {
        $user = DB::table('users')->find($userId);
        return $user?->last_seen_at;
    }

    /**
     * 高亮搜索词
     */
    private function highlightSearchTerm(string $text, string $query): string
    {
        return str_ireplace($query, "<mark>{$query}</mark>", $text);
    }

    /**
     * 获取用户通知设置
     */
    private function getUserNotificationSettings($user): array
    {
        // 这里应该从数据库或配置中获取用户设置
        // 暂时返回默认设置
        return [
            'email_notifications' => true,
            'push_notifications' => true,
            'sound_enabled' => true,
            'desktop_notifications' => true,
            'order_updates' => true,
            'chat_messages' => true,
            'system_announcements' => true
        ];
    }

    /**
     * 保存用户通知设置
     */
    private function saveUserNotificationSettings($user, array $settings): void
    {
        // 这里应该保存到数据库
        Log::info('User notification settings updated', [
            'user_id' => $user->id,
            'settings' => $settings
        ]);
    }
}