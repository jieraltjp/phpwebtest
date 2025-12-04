<?php

namespace App\Events\Product;

use App\Events\AbstractEvent;
use App\Models\Product;

class ProductViewedEvent extends AbstractEvent
{
    public function __construct(Product $product, array $metadata = [])
    {
        $data = [
            'product_id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $product->price,
            'currency' => $product->currency,
            'stock_quantity' => $product->stock_quantity,
            'category' => $product->category,
            'viewer_id' => auth()->id(),
            'viewer_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now()->toISOString(),
            'referrer' => request()->header('referer'),
            'session_id' => session()->getId(),
        ];

        $defaultMetadata = [
            'source' => 'product_view',
            'category' => 'user_activity',
            'importance' => 'low'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), false, 1);
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

    public function getViewerId(): ?int
    {
        return $this->getDataField('viewer_id');
    }

    public function getViewerIp(): string
    {
        return $this->getDataField('viewer_ip');
    }

    public function getUserAgent(): string
    {
        return $this->getDataField('user_agent');
    }

    public function getViewedAt(): string
    {
        return $this->getDataField('viewed_at');
    }

    public function getReferrer(): ?string
    {
        return $this->getDataField('referrer');
    }

    public function getSessionId(): string
    {
        return $this->getDataField('session_id');
    }

    public function isViewedByAuthenticatedUser(): bool
    {
        return $this->getViewerId() !== null;
    }

    public function isViewedByGuest(): bool
    {
        return !$this->isViewedByAuthenticatedUser();
    }

    public function isInStock(): bool
    {
        return $this->getStockQuantity() > 0;
    }
}