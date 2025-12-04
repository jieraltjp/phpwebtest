<?php

declare(strict_types=1);

namespace App\Domain\Product\Events;

use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class ProductCreated extends AbstractDomainEvent
{
    public function __construct(
        ProductId $productId,
        private string $name,
        private string $description,
        private float $price,
        private string $currency
    ) {
        parent::__construct($productId->toString(), 'product_created', [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'currency' => $currency,
        ]);
    }

    public static function create(
        ProductId $productId,
        string $name,
        string $description,
        float $price,
        string $currency
    ): self {
        return new self($productId, $name, $description, $price, $currency);
    }

    public function getProductId(): ProductId
    {
        return ProductId::fromString($this->getAggregateId());
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}