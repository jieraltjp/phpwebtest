<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class OrderStatusChanged extends AbstractDomainEvent
{
    public function __construct(
        OrderId $orderId,
        private string $oldStatus,
        private string $newStatus,
        private ?string $reason = null,
        private ?string $changedBy = null
    ) {
        parent::__construct($orderId->toString(), 'order_status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'changed_by' => $changedBy,
            'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public static function create(
        OrderId $orderId,
        string $oldStatus,
        string $newStatus,
        ?string $reason = null,
        ?string $changedBy = null
    ): self {
        return new self($orderId, $oldStatus, $newStatus, $reason, $changedBy);
    }

    public function getOrderId(): OrderId
    {
        return OrderId::fromString($this->getAggregateId());
    }

    public function getOldStatus(): string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getChangedBy(): ?string
    {
        return $this->changedBy;
    }

    public function isProgressing(): bool
    {
        $statusOrder = [
            'pending' => 1,
            'confirmed' => 2,
            'processing' => 3,
            'shipped' => 4,
            'delivered' => 5,
            'cancelled' => 99,
            'refunded' => 98,
            'on_hold' => 0,
        ];

        $oldOrder = $statusOrder[$this->oldStatus] ?? 0;
        $newOrder = $statusOrder[$this->newStatus] ?? 0;

        return $newOrder > $oldOrder && !in_array($this->newStatus, ['cancelled', 'refunded'], true);
    }

    public function isCancellation(): bool
    {
        return $this->newStatus === 'cancelled';
    }

    public function isRefund(): bool
    {
        return $this->newStatus === 'refunded';
    }

    public function requiresNotification(): bool
    {
        // These status changes typically require customer notification
        $notificationRequired = [
            'confirmed',
            'shipped',
            'delivered',
            'cancelled',
            'refunded',
            'on_hold',
        ];

        return in_array($this->newStatus, $notificationRequired, true);
    }

    public function getCustomerMessage(): string
    {
        return match ($this->newStatus) {
            'confirmed' => '您的订单已确认，我们正在准备发货。',
            'processing' => '您的订单正在处理中。',
            'shipped' => '您的订单已发货，请注意查收。',
            'delivered' => '您的订单已送达，感谢您的购买。',
            'cancelled' => '您的订单已取消。',
            'refunded' => '您的订单已退款。',
            'on_hold' => '您的订单暂时暂停，我们会尽快处理。',
            default => '订单状态已更新。',
        };
    }

    public function requiresInventoryUpdate(): bool
    {
        // These status changes affect inventory
        return in_array($this->newStatus, ['confirmed', 'cancelled'], true);
    }

    public function requiresPaymentProcessing(): bool
    {
        // These status changes may require payment actions
        return in_array($this->newStatus, ['confirmed', 'cancelled', 'refunded'], true);
    }
}