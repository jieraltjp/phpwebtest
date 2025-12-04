<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class OrderDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $customerId,
        public readonly string $customerEmail,
        public readonly array $items,
        public readonly float $totalAmount,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?string $shippingAddress,
        public readonly ?string $billingAddress,
        public readonly ?string $notes,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
        public readonly ?string $trackingNumber,
        public readonly string $statusDisplayName,
        public readonly string $statusColorClass,
        public readonly bool $isBulkOrder,
        public readonly bool $isHighValueOrder
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'customer_email' => $this->customerEmail,
            'items' => $this->items,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'tracking_number' => $this->trackingNumber,
            'status_display_name' => $this->statusDisplayName,
            'status_color_class' => $this->statusColorClass,
            'is_bulk_order' => $this->isBulkOrder,
            'is_high_value_order' => $this->isHighValueOrder,
            'item_count' => count($this->items),
            'formatted_total_amount' => $this->getFormattedTotalAmount(),
        ];
    }

    public function getFormattedTotalAmount(): string
    {
        $symbol = match ($this->currency) {
            'CNY', 'JPY' => '¥',
            'USD' => '$',
            default => '',
        };

        return $symbol . number_format($this->totalAmount, 2);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing', 'shipped', 'on_hold'], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function requiresShipping(): bool
    {
        return in_array($this->status, ['confirmed', 'processing', 'shipped'], true);
    }

    public function canBeModified(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing', 'on_hold'], true);
    }

    public function hasTrackingNumber(): bool
    {
        return !empty($this->trackingNumber);
    }

    public function getDaysSinceCreation(): int
    {
        return $this->createdAt->diff(new \DateTimeImmutable())->days;
    }
}