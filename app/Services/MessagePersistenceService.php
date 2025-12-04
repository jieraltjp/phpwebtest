<?php

namespace App\Services;

use App\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 消息持久化服务
 * 
 * 处理WebSocket消息的存储、检索和清理
 * 支持离线消息、消息历史和过期清理
 */
class MessagePersistenceService
{
    private int $maxMessageAge = 7 * 24 * 60 * 60; // 7天
    private int $maxOfflineMessages = 100; // 每用户最大离线消息数
    private array $offlineMessages = []; // 内存存储
    private array $channelHistory = []; // 频道历史

    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * 存储离线消息
     */
    public function storeOfflineMessage(int $userId, array $message): bool
    {
        try {
            $offlineMessage = [
                'id' => uniqid('off_', true),
                'user_id' => $userId,
                'message' => json_encode($message),
                'message_type' => $message['type'] ?? 'unknown',
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addSeconds($this->maxMessageAge)->toISOString()
            ];

            // 存储到内存数组
            if (!isset($this->offlineMessages[$userId])) {
                $this->offlineMessages[$userId] = [];
            }

            array_unshift($this->offlineMessages[$userId], $offlineMessage);

            // 限制消息数量
            if (count($this->offlineMessages[$userId]) > $this->maxOfflineMessages) {
                $this->offlineMessages[$userId] = array_slice($this->offlineMessages[$userId], 0, $this->maxOfflineMessages);
            }

            Log::debug('Offline message stored', [
                'user_id' => $userId,
                'message_id' => $offlineMessage['id'],
                'message_type' => $offlineMessage['message_type']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to store offline message', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 获取用户的离线消息
     */
    public function getOfflineMessages(int $userId, int $limit = 50): array
    {
        try {
            $userMessages = $this->offlineMessages[$userId] ?? [];
            $messages = array_slice($userMessages, 0, $limit);

            $offlineMessages = [];
            foreach ($messages as $messageData) {
                if ($messageData) {
                    $offlineMessages[] = [
                        'id' => $messageData['id'],
                        'message' => json_decode($messageData['message'], true),
                        'message_type' => $messageData['message_type'],
                        'created_at' => $messageData['created_at'],
                        'expires_at' => $messageData['expires_at']
                    ];
                }
            }

            Log::debug('Retrieved offline messages', [
                'user_id' => $userId,
                'count' => count($offlineMessages)
            ]);

            return $offlineMessages;

        } catch (\Exception $e) {
            Log::error('Failed to get offline messages', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * 清除用户的离线消息
     */
    public function clearOfflineMessages(int $userId): bool
    {
        try {
            $count = isset($this->offlineMessages[$userId]) ? count($this->offlineMessages[$userId]) : 0;
            unset($this->offlineMessages[$userId]);

            Log::info('Offline messages cleared', [
                'user_id' => $userId,
                'deleted_count' => $count
            ]);

            return $count > 0;

        } catch (\Exception $e) {
            Log::error('Failed to clear offline messages', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 标记消息为已读
     */
    public function markMessageAsRead(int $userId, string $messageId): bool
    {
        try {
            // 这里应该更新数据库中的已读状态
            // 暂时只记录日志
            Log::info('Message marked as read', [
                'user_id' => $userId,
                'message_id' => $messageId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to mark message as read', [
                'user_id' => $userId,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 存储频道消息历史
     */
    public function storeChannelMessage(string $channel, array $message): bool
    {
        try {
            $channelMessage = [
                'id' => uniqid('ch_', true),
                'channel' => $channel,
                'message' => json_encode($message),
                'message_type' => $message['type'] ?? 'unknown',
                'created_at' => now()->toISOString()
            ];

            // 存储到Redis有序集合，按时间排序
            $key = "channel_history:{$channel}";
            $score = now()->timestamp;
            Redis::zadd($key, $score, json_encode($channelMessage));

            // 限制历史消息数量
            Redis::zremrangebyrank($key, 0, -1001); // 保留最新1000条

            // 设置过期时间
            Redis::expire($key, $this->maxMessageAge);

            Log::debug('Channel message stored', [
                'channel' => $channel,
                'message_id' => $channelMessage['id'],
                'message_type' => $channelMessage['message_type']
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to store channel message', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 获取频道消息历史
     */
    public function getChannelHistory(string $channel, int $limit = 50, ?string $before = null): array
    {
        try {
            $key = "channel_history:{$channel}";

            if ($before) {
                $beforeTime = new Carbon($before);
                $maxScore = $beforeTime->timestamp;
                $messages = Redis::zrevrangebyscore($key, $maxScore, '-inf', ['limit' => [0, $limit]]);
            } else {
                $messages = Redis::zrevrange($key, 0, $limit - 1);
            }

            $channelMessages = [];
            foreach ($messages as $messageData) {
                $channelMessage = json_decode($messageData, true);
                if ($channelMessage) {
                    $channelMessages[] = [
                        'id' => $channelMessage['id'],
                        'message' => json_decode($channelMessage['message'], true),
                        'message_type' => $channelMessage['message_type'],
                        'created_at' => $channelMessage['created_at']
                    ];
                }
            }

            Log::debug('Retrieved channel history', [
                'channel' => $channel,
                'count' => count($channelMessages)
            ]);

            return $channelMessages;

        } catch (\Exception $e) {
            Log::error('Failed to get channel history', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * 存储聊天消息
     */
    public function storeChatMessage(int $fromUserId, int $toUserId, string $message, string $chatType = 'direct'): ?string
    {
        try {
            $chatMessage = [
                'id' => uniqid('chat_', true),
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'message' => $message,
                'chat_type' => $chatType,
                'created_at' => now()->toISOString(),
                'read_at' => null
            ];

            // 存储到数据库
            $id = DB::table('chat_messages')->insertGetId([
                'id' => $chatMessage['id'],
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'message' => $message,
                'chat_type' => $chatType,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 为接收者存储离线消息
            $this->storeOfflineMessage($toUserId, [
                'type' => 'chat_message',
                'data' => $chatMessage
            ]);

            Log::info('Chat message stored', [
                'message_id' => $chatMessage['id'],
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'chat_type' => $chatType
            ]);

            return $chatMessage['id'];

        } catch (\Exception $e) {
            Log::error('Failed to store chat message', [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * 获取聊天历史
     */
    public function getChatHistory(int $userId1, int $userId2, int $limit = 50, ?string $before = null): array
    {
        try {
            $query = DB::table('chat_messages')
                ->where(function ($q) use ($userId1, $userId2) {
                    $q->where('from_user_id', $userId1)
                      ->where('to_user_id', $userId2);
                })
                ->orWhere(function ($q) use ($userId1, $userId2) {
                    $q->where('from_user_id', $userId2)
                      ->where('to_user_id', $userId1);
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($before) {
                $query->where('created_at', '<', new Carbon($before));
            }

            $messages = $query->get()->reverse(); // 按时间正序返回

            $chatHistory = [];
            foreach ($messages as $message) {
                $chatHistory[] = [
                    'id' => $message->id,
                    'from_user_id' => $message->from_user_id,
                    'to_user_id' => $message->to_user_id,
                    'message' => $message->message,
                    'chat_type' => $message->chat_type,
                    'created_at' => $message->created_at->toISOString(),
                    'read_at' => $message->read_at?->toISOString()
                ];
            }

            Log::debug('Retrieved chat history', [
                'user1' => $userId1,
                'user2' => $userId2,
                'count' => count($chatHistory)
            ]);

            return $chatHistory;

        } catch (\Exception $e) {
            Log::error('Failed to get chat history', [
                'user1' => $userId1,
                'user2' => $userId2,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * 标记聊天消息为已读
     */
    public function markChatMessagesAsRead(int $userId, int $fromUserId): int
    {
        try {
            $updated = DB::table('chat_messages')
                ->where('from_user_id', $fromUserId)
                ->where('to_user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            Log::info('Chat messages marked as read', [
                'user_id' => $userId,
                'from_user_id' => $fromUserId,
                'count' => $updated
            ]);

            return $updated;

        } catch (\Exception $e) {
            Log::error('Failed to mark chat messages as read', [
                'user_id' => $userId,
                'from_user_id' => $fromUserId,
                'error' => $e->getMessage()
            ]);

            return 0;
        }
    }

    /**
     * 获取未读消息统计
     */
    public function getUnreadMessageStats(int $userId): array
    {
        try {
            // 离线消息数量
            $offlineCount = Redis::llen("offline_messages:{$userId}");

            // 未读聊天消息数量
            $unreadChatCount = DB::table('chat_messages')
                ->where('to_user_id', $userId)
                ->whereNull('read_at')
                ->count();

            $stats = [
                'offline_messages' => $offlineCount,
                'unread_chat_messages' => $unreadChatCount,
                'total_unread' => $offlineCount + $unreadChatCount
            ];

            Log::debug('Retrieved unread message stats', [
                'user_id' => $userId,
                'stats' => $stats
            ]);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get unread message stats', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'offline_messages' => 0,
                'unread_chat_messages' => 0,
                'total_unread' => 0
            ];
        }
    }

    /**
     * 清理过期消息
     */
    public function cleanupExpiredMessages(): array
    {
        $cleaned = [
            'offline_messages' => 0,
            'channel_messages' => 0,
            'chat_messages' => 0
        ];

        try {
            // 清理过期离线消息
            $offlineKeys = Redis::keys('offline_messages:*');
            foreach ($offlineKeys as $key) {
                $messages = Redis::lrange($key, 0, -1);
                $validMessages = [];

                foreach ($messages as $messageData) {
                    $message = json_decode($messageData, true);
                    if ($message && new Carbon($message['expires_at']) > now()) {
                        $validMessages[] = $messageData;
                    } else {
                        $cleaned['offline_messages']++;
                    }
                }

                // 重新存储有效消息
                Redis::del($key);
                if (!empty($validMessages)) {
                    foreach ($validMessages as $message) {
                        Redis::lpush($key, $message);
                    }
                }
            }

            // 清理过期频道消息
            $channelKeys = Redis::keys('channel_history:*');
            foreach ($channelKeys as $key) {
                $cutoff = now()->subSeconds($this->maxMessageAge)->timestamp;
                $removed = Redis::zremrangebyscore($key, '-inf', $cutoff);
                $cleaned['channel_messages'] += $removed;
            }

            // 清理过期聊天消息（数据库中超过30天的）
            $deleted = DB::table('chat_messages')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();
            $cleaned['chat_messages'] = $deleted;

            Log::info('Expired messages cleaned', [
                'cleaned' => $cleaned
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup expired messages', [
                'error' => $e->getMessage()
            ]);
        }

        return $cleaned;
    }

    /**
     * 获取消息统计信息
     */
    public function getMessageStats(): array
    {
        try {
            // 离线消息统计
            $offlineKeys = Redis::keys('offline_messages:*');
            $totalOfflineMessages = 0;
            foreach ($offlineKeys as $key) {
                $totalOfflineMessages += Redis::llen($key);
            }

            // 频道消息统计
            $channelKeys = Redis::keys('channel_history:*');
            $totalChannelMessages = 0;
            foreach ($channelKeys as $key) {
                $totalChannelMessages += Redis::zcard($key);
            }

            // 聊天消息统计
            $chatStats = DB::table('chat_messages')
                ->selectRaw('
                    COUNT(*) as total_messages,
                    COUNT(CASE WHEN read_at IS NULL THEN 1 END) as unread_messages,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as messages_24h,
                    COUNT(CASE WHEN created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as messages_7d
                ')
                ->first();

            return [
                'offline_messages' => [
                    'total' => $totalOfflineMessages,
                    'users_with_messages' => count($offlineKeys)
                ],
                'channel_messages' => [
                    'total' => $totalChannelMessages,
                    'channels' => count($channelKeys)
                ],
                'chat_messages' => [
                    'total' => $chatStats->total_messages ?? 0,
                    'unread' => $chatStats->unread_messages ?? 0,
                    'last_24h' => $chatStats->messages_24h ?? 0,
                    'last_7d' => $chatStats->messages_7d ?? 0
                ],
                'storage_info' => [
                    'redis_memory' => Redis::info('memory')['used_memory_human'] ?? 'N/A',
                    'max_message_age_days' => $this->maxMessageAge / (24 * 60 * 60),
                    'max_offline_messages_per_user' => $this->maxOfflineMessages
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get message stats', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }
}