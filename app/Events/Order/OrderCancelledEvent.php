<?php

namespace App\Events\Order;

use App\Events\AbstractEvent;
use App\Models\Order;

class OrderCancelledEvent extends AbstractEvent
{
    public function __construct(Order $order, string $reason = null, array $metadata = [])
    {
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'cancellation_reason' => $reason,
            'cancelled_at' => now()->toISOString(),
            'cancelled_by' => auth()->id() ?? 'system',
            'items_count' => $order->items->count(),
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price
                ];
            })->toArray(),
        ];

        $defaultMetadata = [
            'source' => 'order_cancellation',
            'category' => 'order_lifecycle',
            'importance' => 'high',
            'requires_notification' => true,
            'requires_refund' => $order->payment_status === 'paid'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), true, 10);
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

    public function getCancellationReason(): ?string
    {
        return $this->getDataField('cancellation_reason');
    }

    public function getCancelledAt(): string
    {
        return $this->getDataField('cancelled_at');
    }

    public function getCancelledBy(): string
    {
        return $this->getDataField('cancelled_by');
    }

    public function getItemsCount(): int
    {
        return $this->getDataField('items_count');
    }

    public function getItems(): array
    {
        return $this->getDataField('items');
    }

    public function requiresRefund(): bool
    {
        return $this->getMetadataField('requires_refund', false);
    }

    public function requiresNotification(): bool
    {
        return $this->getMetadataField('requires_notification', false);
    }

    public function getProductIds(): array
    {
        return array_unique(array_column($this->getItems(), 'product_id'));
    }

    public function getTotalItemsQuantity(): int
    {
        return array_sum(array_column($this->getItems(), 'quantity'));
    }
}