<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class OrderItem extends AbstractValueObject
{
    private string $productId;
    private string $productName;
    private int $quantity;
    private float $unitPrice;
    private string $currency;
    private float $totalPrice;
    private array $specifications;

    public function __construct(
        string $productId,
        string $productName,
        int $quantity,
        float $unitPrice,
        string $currency = 'CNY',
        array $specifications = []
    ) {
        $this->validate($productId, $productName, $quantity, $unitPrice, $currency);
        
        $this->productId = $productId;
        $this->productName = $productName;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->currency = $currency;
        $this->totalPrice = $quantity * $unitPrice;
        $this->specifications = $specifications;
    }

    public static function create(
        string $productId,
        string $productName,
        int $quantity,
        float $unitPrice,
        string $currency = 'CNY',
        array $specifications = []
    ): self {
        return new self($productId, $productName, $quantity, $unitPrice, $currency, $specifications);
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    public function updateQuantity(int $newQuantity): self
    {
        if ($newQuantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        return new self(
            $this->productId,
            $this->productName,
            $newQuantity,
            $this->unitPrice,
            $this->currency,
            $this->specifications
        );
    }

    public function updateUnitPrice(float $newUnitPrice): self
    {
        if ($newUnitPrice < 0) {
            throw new \InvalidArgumentException('Unit price cannot be negative');
        }

        return new self(
            $this->productId,
            $this->productName,
            $this->quantity,
            $newUnitPrice,
            $this->currency,
            $this->specifications
        );
    }

    public function updateProductName(string $newProductName): self
    {
        if (empty(trim($newProductName))) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        return new self(
            $this->productId,
            trim($newProductName),
            $this->quantity,
            $this->unitPrice,
            $this->currency,
            $this->specifications
        );
    }

    public function updateSpecifications(array $newSpecifications): self
    {
        return new self(
            $this->productId,
            $this->productName,
            $this->quantity,
            $this->unitPrice,
            $this->currency,
            $newSpecifications
        );
    }

    public function isSameProduct(string $productId): bool
    {
        return $this->productId === $productId;
    }

    public function hasSamePrice(float $price, string $currency): bool
    {
        return $this->unitPrice === $price && $this->currency === $currency;
    }

    public function isBulkOrder(): bool
    {
        return $this->quantity >= 100;
    }

    public function isHighValue(): bool
    {
        return $this->totalPrice >= 10000;
    }

    public function getFormattedUnitPrice(): string
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->unitPrice, 2);
    }

    public function getFormattedTotalPrice(): string
    {
        $symbol = $this->getCurrencySymbol();
        return $symbol . number_format($this->totalPrice, 2);
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'currency' => $this->currency,
            'total_price' => $this->totalPrice,
            'specifications' => $this->specifications,
            'formatted_unit_price' => $this->getFormattedUnitPrice(),
            'formatted_total_price' => $this->getFormattedTotalPrice(),
            'is_bulk_order' => $this->isBulkOrder(),
            'is_high_value' => $this->isHighValue(),
        ];
    }

    protected function getValue(): mixed
    {
        return [
            'productId' => $this->productId,
            'productName' => $this->productName,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'currency' => $this->currency,
            'totalPrice' => $this->totalPrice,
            'specifications' => $this->specifications,
        ];
    }

    private function validate(
        string $productId,
        string $productName,
        int $quantity,
        float $unitPrice,
        string $currency
    ): void {
        if (empty(trim($productId))) {
            throw new \InvalidArgumentException('Product ID cannot be empty');
        }

        if (empty(trim($productName))) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }

        if ($unitPrice < 0) {
            throw new \InvalidArgumentException('Unit price cannot be negative');
        }

        if (!in_array($currency, ['CNY', 'JPY', 'USD'], true)) {
            throw new \InvalidArgumentException('Invalid currency. Supported currencies: CNY, JPY, USD');
        }

        if (strlen($productName) > 255) {
            throw new \InvalidArgumentException('Product name cannot exceed 255 characters');
        }

        if ($quantity > 10000) {
            throw new \InvalidArgumentException('Quantity cannot exceed 10,000 items');
        }
    }

    private function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'CNY', 'JPY' => '¥',
            'USD' => '$',
            default => '',
        };
    }
}