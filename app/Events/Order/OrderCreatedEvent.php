<?php

namespace App\Events\Order;

use App\Events\AbstractEvent;
use App\Models\Order;

class OrderCreatedEvent extends AbstractEvent
{
    public function __construct(Order $order, array $metadata = [])
    {
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => $order->status,
            'items_count' => $order->items->count(),
            'items' => $order->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price
                ];
            })->toArray(),
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'created_at' => $order->created_at->toISOString(),
        ];

        $defaultMetadata = [
            'source' => 'order_creation',
            'category' => 'order_lifecycle',
            'importance' => 'high',
            'requires_notification' => true
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

    public function getCurrency(): string
    {
        return $this->getDataField('currency');
    }

    public function getStatus(): string
    {
        return $this->getDataField('status');
    }

    public function getItemsCount(): int
    {
        return $this->getDataField('items_count');
    }

    public function getItems(): array
    {
        return $this->getDataField('items');
    }

    public function getShippingAddress(): ?string
    {
        return $this->getDataField('shipping_address');
    }

    public function getBillingAddress(): ?string
    {
        return $this->getDataField('billing_address');
    }

    public function getCreatedAt(): string
    {
        return $this->getDataField('created_at');
    }

    public function getTotalItemsQuantity(): int
    {
        return array_sum(array_column($this->getItems(), 'quantity'));
    }

    public function getProductIds(): array
    {
        return array_unique(array_column($this->getItems(), 'product_id'));
    }
}