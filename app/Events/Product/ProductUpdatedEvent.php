<?php

namespace App\Events\Product;

use App\Events\AbstractEvent;
use App\Models\Product;

class ProductUpdatedEvent extends AbstractEvent
{
    public function __construct(Product $product, array $originalData, array $updatedData, array $metadata = [])
    {
        $data = [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'original_data' => $originalData,
            'updated_data' => $updatedData,
            'changed_fields' => array_keys(array_diff_assoc($updatedData, $originalData)),
            'updated_by' => auth()->id() ?? 'system',
            'updated_at' => now()->toISOString(),
            'current_stock' => $product->stock_quantity,
            'previous_stock' => $originalData['stock_quantity'] ?? $product->stock_quantity,
        ];

        $defaultMetadata = [
            'source' => 'product_update',
            'category' => 'product_lifecycle',
            'importance' => 'low',
            'stock_changed' => isset($originalData['stock_quantity']) && $originalData['stock_quantity'] !== $product->stock_quantity,
            'price_changed' => isset($originalData['price']) && $originalData['price'] !== $product->price
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), false, 3);
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

    public function getOriginalData(): array
    {
        return $this->getDataField('original_data');
    }

    public function getUpdatedData(): array
    {
        return $this->getDataField('updated_data');
    }

    public function getChangedFields(): array
    {
        return $this->getDataField('changed_fields');
    }

    public function getUpdatedBy(): string
    {
        return $this->getDataField('updated_by');
    }

    public function getUpdatedAt(): string
    {
        return $this->getDataField('updated_at');
    }

    public function getCurrentStock(): int
    {
        return $this->getDataField('current_stock');
    }

    public function getPreviousStock(): int
    {
        return $this->getDataField('previous_stock');
    }

    public function hasFieldChanged(string $field): bool
    {
        return in_array($field, $this->getChangedFields());
    }

    public function hasStockChanged(): bool
    {
        return $this->getMetadataField('stock_changed', false);
    }

    public function hasPriceChanged(): bool
    {
        return $this->getMetadataField('price_changed', false);
    }

    public function getStockChange(): int
    {
        return $this->getCurrentStock() - $this->getPreviousStock();
    }

    public function isStockDecreased(): bool
    {
        return $this->getStockChange() < 0;
    }

    public function isStockIncreased(): bool
    {
        return $this->getStockChange() > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->getCurrentStock() <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->getCurrentStock() > 0 && $this->getCurrentStock() <= 10;
    }
}