<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final class CreateOrderDTO
{
    public function __construct(
        public readonly string $customerId,
        public readonly string $customerEmail,
        public readonly array $items,
        public readonly string $currency = 'CNY'
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['customer_id'],
            $data['customer_email'],
            $data['items'],
            $data['currency'] ?? 'CNY'
        );
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'customer_email' => $this->customerEmail,
            'items' => $this->items,
            'currency' => $this->currency,
        ];
    }

    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->customerId))) {
            $errors[] = 'Customer ID is required';
        }

        if (empty(trim($this->customerEmail))) {
            $errors[] = 'Customer email is required';
        } elseif (!filter_var($this->customerEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid customer email format';
        }

        if (empty($this->items)) {
            $errors[] = 'Order must contain at least one item';
        } else {
            foreach ($this->items as $index => $item) {
                $itemErrors = $this->validateOrderItem($item, $index);
                $errors = array_merge($errors, $itemErrors);
            }
        }

        $validCurrencies = ['CNY', 'JPY', 'USD'];
        if (!in_array($this->currency, $validCurrencies, true)) {
            $errors[] = 'Invalid currency';
        }

        return $errors;
    }

    private function validateOrderItem(array $item, int $index): array
    {
        $errors = [];
        $prefix = "Item {$index}: ";

        if (empty(trim($item['product_id'] ?? ''))) {
            $errors[] = $prefix . 'Product ID is required';
        }

        if (empty(trim($item['product_name'] ?? ''))) {
            $errors[] = $prefix . 'Product name is required';
        }

        if (!isset($item['quantity']) || $item['quantity'] <= 0) {
            $errors[] = $prefix . 'Quantity must be positive';
        }

        if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
            $errors[] = $prefix . 'Unit price cannot be negative';
        }

        if (isset($item['currency']) && !in_array($item['currency'], ['CNY', 'JPY', 'USD'], true)) {
            $errors[] = $prefix . 'Invalid currency';
        }

        return $errors;
    }

    public function getTotalAmount(): float
    {
        return array_reduce($this->items, function ($sum, $item) {
            return $sum + ($item['unit_price'] * $item['quantity']);
        }, 0);
    }

    public function getTotalQuantity(): int
    {
        return array_reduce($this->items, function ($sum, $item) {
            return $sum + $item['quantity'];
        }, 0);
    }

    public function isBulkOrder(): bool
    {
        return $this->getTotalAmount() >= 50000 || $this->getTotalQuantity() >= 20;
    }

    public function isHighValueOrder(): bool
    {
        return $this->getTotalAmount() >= 100000;
    }
}