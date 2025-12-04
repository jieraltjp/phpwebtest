<?php

namespace App\Events\Product;

use App\Events\AbstractEvent;
use App\Models\Product;

class ProductCreatedEvent extends AbstractEvent
{
    public function __construct(Product $product, array $metadata = [])
    {
        $data = [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'currency' => $product->currency,
            'stock_quantity' => $product->stock_quantity,
            'category' => $product->category,
            'status' => $product->status,
            'created_by' => auth()->id() ?? 'system',
            'created_at' => $product->created_at->toISOString(),
        ];

        $defaultMetadata = [
            'source' => 'product_creation',
            'category' => 'product_lifecycle',
            'importance' => 'medium'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), false, 5);
    }

    public function getProductId(): int
    {
        return $this->getDataField('product_id');
    }

    public function getSku(): string
    {
        return $this->getDataField('sku');
    }

    public function getName(): string
    {
        return $this->getDataField('name');
    }

    public function getDescription(): ?string
    {
        return $this->getDataField('description');
    }

    public function getPrice(): float
    {
        return $this->getDataField('price');
    }

    public function getCurrency(): string
    {
        return $this->getDataField('currency');
    }

    public function getStockQuantity(): int
    {
        return $this->getDataField('stock_quantity');
    }

    public function getCategory(): ?string
    {
        return $this->getDataField('category');
    }

    public function getStatus(): string
    {
        return $this->getDataField('status');
    }

    public function getCreatedBy(): string
    {
        return $this->getDataField('created_by');
    }

    public function getCreatedAt(): string
    {
        return $this->getDataField('created_at');
    }

    public function isInStock(): bool
    {
        return $this->getStockQuantity() > 0;
    }

    public function isLowStock(): bool
    {
        return $this->getStockQuantity() > 0 && $this->getStockQuantity() <= 10;
    }
}