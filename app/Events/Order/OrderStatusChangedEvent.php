<?php

namespace App\Events\Order;

use App\Events\AbstractEvent;
use App\Models\Order;

class OrderStatusChangedEvent extends AbstractEvent
{
    public function __construct(Order $order, string $oldStatus, string $newStatus, array $metadata = [])
    {
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'status_change_time' => now()->toISOString(),
            'changed_by' => auth()->id() ?? 'system',
            'reason' => $metadata['reason'] ?? null,
            'items_count' => $order->items->count(),
        ];

        $defaultMetadata = [
            'source' => 'order_status_change',
            'category' => 'order_lifecycle',
            'importance' => 'high',
            'requires_notification' => in_array($newStatus, ['confirmed', 'shipped', 'delivered', 'cancelled'])
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), true, 8);
    }

    public function getOrderId(): int
    {
        return $this->getDataField('order_id');
    }

    public function getOrderNumber(): string
    {
        return $this->getDataField('order_number');
    }

    public function getUserId(): int
    {
        return $this->getDataField('user_id');
    }

    public function getTotalAmount(): float
    {
        return $this->getDataField('total_amount');
    }

    public function getCurrency(): string
    {
        return $this->getDataField('currency');
    }

    public function getOldStatus(): string
    {
        return $this->getDataField('old_status');
    }

    public function getNewStatus(): string
    {
        return $this->getDataField('new_status');
    }

    public function getStatusChangeTime(): string
    {
        return $this->getDataField('status_change_time');
    }

    public function getChangedBy(): string
    {
        return $this->getDataField('changed_by');
    }

    public function getReason(): ?string
    {
        return $this->getDataField('reason');
    }

    public function getItemsCount(): int
    {
        return $this->getDataField('items_count');
    }

    public function isStatusUpgrade(): bool
    {
        $statusFlow = [
            'pending' => 1,
            'confirmed' => 2,
            'processing' => 3,
            'shipped' => 4,
            'delivered' => 5,
            'cancelled' => 0
        ];

        return ($statusFlow[$this->getNewStatus()] ?? 0) > ($statusFlow[$this->getOldStatus()] ?? 0);
    }

    public function requiresNotification(): bool
    {
        return $this->getMetadataField('requires_notification', false);
    }
}