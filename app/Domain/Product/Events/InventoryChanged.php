<?php

declare(strict_types=1);

namespace App\Domain\Product\Events;

use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Abstractions\AbstractDomainEvent;

final class InventoryChanged extends AbstractDomainEvent
{
    public function __construct(
        ProductId $productId,
        private int $oldQuantity,
        private int $newQuantity,
        private string $reason,
        private ?string $referenceId = null
    ) {
        parent::__construct($productId->toString(), 'inventory_changed', [
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'change' => $newQuantity - $oldQuantity,
            'reason' => $reason,
            'reference_id' => $referenceId,
        ]);
    }

    public static function create(
        ProductId $productId,
        int $oldQuantity,
        int $newQuantity,
        string $reason,
        ?string $referenceId = null
    ): self {
        return new self($productId, $oldQuantity, $newQuantity, $reason, $referenceId);
    }

    public static function forOrder(
        ProductId $productId,
        int $oldQuantity,
        int $newQuantity,
        string $orderId
    ): self {
        return new self(
            $productId,
            $oldQuantity,
            $newQuantity,
            'order_fulfillment',
            $orderId
        );
    }

    public static function forRestock(
        ProductId $productId,
        int $oldQuantity,
        int $newQuantity,
        string $restockId
    ): self {
        return new self(
            $productId,
            $oldQuantity,
            $newQuantity,
            'restock',
            $restockId
        );
    }

    public static function forAdjustment(
        ProductId $productId,
        int $oldQuantity,
        int $newQuantity,
        string $reason
    ): self {
        return new self(
            $productId,
            $oldQuantity,
            $newQuantity,
            $reason
        );
    }

    public function getProductId(): ProductId
    {
        return ProductId::fromString($this->getAggregateId());
    }

    public function getOldQuantity(): int
    {
        return $this->oldQuantity;
    }

    public function getNewQuantity(): int
    {
        return $this->newQuantity;
    }

    public function getChange(): int
    {
        return $this->newQuantity - $this->oldQuantity;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    public function isIncrease(): bool
    {
        return $this->getChange() > 0;
    }

    public function isDecrease(): bool
    {
        return $this->getChange() < 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->newQuantity <= 0;
    }

    public function needsRestock(): bool
    {
        return $this->newQuantity <= 10; // Business rule
    }
}