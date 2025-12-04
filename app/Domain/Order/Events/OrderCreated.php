<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderItem;
use App\Domain\Abstractions\AbstractDomainEvent;

final class OrderCreated extends AbstractDomainEvent
{
    public function __construct(
        OrderId $orderId,
        private string $customerId,
        private array $items,
        private float $totalAmount,
        private string $currency,
        private string $customerEmail
    ) {
        parent::__construct($orderId->toString(), 'order_created', [
            'customer_id' => $customerId,
            'items' => array_map(fn($item) => $item->toArray(), $items),
            'total_amount' => $totalAmount,
            'currency' => $currency,
            'customer_email' => $customerEmail,
            'item_count' => count($items),
        ]);
    }

    public static function create(
        OrderId $orderId,
        string $customerId,
        array $items,
        float $totalAmount,
        string $currency,
        string $customerEmail
    ): self {
        return new self($orderId, $customerId, $items, $totalAmount, $currency, $customerEmail);
    }

    public function getOrderId(): OrderId
    {
        return OrderId::fromString($this->getAggregateId());
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function isBulkOrder(): bool
    {
        return $this->getTotalAmount() >= 50000 || $this->getItemCount() >= 20;
    }

    public function isHighValueOrder(): bool
    {
        return $this->getTotalAmount() >= 100000;
    }

    public function getItemsSummary(): array
    {
        $summary = [];
        
        foreach ($this->items as $item) {
            if ($item instanceof OrderItem) {
                $summary[] = [
                    'product_id' => $item->getProductId(),
                    'product_name' => $item->getProductName(),
                    'quantity' => $item->getQuantity(),
                    'unit_price' => $item->getUnitPrice(),
                    'total_price' => $item->getTotalPrice(),
                ];
            }
        }

        return $summary;
    }
}