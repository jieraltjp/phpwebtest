<?php

declare(strict_types=1);

namespace App\Domain\Purchase;

use App\Domain\Purchase\ValueObjects\BulkPurchaseId;
use App\Domain\Order\ValueObjects\OrderItem;
use App\Domain\Abstractions\AbstractAggregateRoot;

final class BulkPurchase extends AbstractAggregateRoot
{
    private BulkPurchaseId $id;
    private string $customerId;
    private string $customerEmail;
    private array $items;
    private float $totalAmount;
    private string $currency;
    private float $discountRate;
    private float $discountedAmount;
    private string $status;
    private ?string $notes;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    private function __construct(
        BulkPurchaseId $id,
        string $customerId,
        string $customerEmail,
        array $items,
        string $currency = 'CNY'
    ) {
        parent::__construct($id->toString());
        $this->id = $id;
        $this->customerId = $customerId;
        $this->customerEmail = $customerEmail;
        $this->items = $items;
        $this->currency = $currency;
        $this->status = 'pending';
        $this->notes = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;

        $this->calculateTotalAndDiscount();
    }

    public static function create(
        BulkPurchaseId $id,
        string $customerId,
        string $customerEmail,
        array $items,
        string $currency = 'CNY'
    ): self {
        if (empty($items)) {
            throw new \InvalidArgumentException('Bulk purchase must contain at least one item');
        }

        if (count($items) > 50) {
            throw new \InvalidArgumentException('Bulk purchase cannot contain more than 50 items');
        }

        return new self($id, $customerId, $customerEmail, $items, $currency);
    }

    public function getId(): BulkPurchaseId
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
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

    public function getDiscountRate(): float
    {
        return $this->discountRate;
    }

    public function getDiscountedAmount(): float
    {
        return $this->discountedAmount;
    }

    public function getFinalAmount(): float
    {
        return $this->totalAmount - $this->discountedAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }

    public function getTotalQuantity(): int
    {
        return array_reduce($this->items, function ($sum, OrderItem $item) {
            return $sum + $item->getQuantity();
        }, 0);
    }

    public function approve(?string $approvedBy = null): void
    {
        if ($this->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending bulk purchases can be approved');
        }

        $this->status = 'approved';
        $this->markAsUpdated();
    }

    public function reject(?string $reason = null, ?string $rejectedBy = null): void
    {
        if ($this->status !== 'pending') {
            throw new \InvalidArgumentException('Only pending bulk purchases can be rejected');
        }

        $this->status = 'rejected';
        $this->notes = $reason;
        $this->markAsUpdated();
    }

    public function convertToOrder(): void
    {
        if ($this->status !== 'approved') {
            throw new \InvalidArgumentException('Only approved bulk purchases can be converted to orders');
        }

        $this->status = 'converted';
        $this->markAsUpdated();
    }

    public function cancel(?string $reason = null): void
    {
        if (!in_array($this->status, ['pending', 'approved'], true)) {
            throw new \InvalidArgumentException('Bulk purchase cannot be cancelled in current status');
        }

        $this->status = 'cancelled';
        $this->notes = $reason;
        $this->markAsUpdated();
    }

    public function isBulkOrder(): bool
    {
        return $this->getTotalAmount() >= 50000 || $this->getItemCount() >= 20;
    }

    public function isHighValue(): bool
    {
        return $this->getTotalAmount() >= 100000;
    }

    private function calculateTotalAndDiscount(): void
    {
        $this->totalAmount = array_reduce($this->items, function ($sum, OrderItem $item) {
            return $sum + $item->getTotalPrice();
        }, 0);

        // Calculate discount based on total amount and quantity
        $this->discountRate = $this->calculateDiscountRate();
        $this->discountedAmount = $this->totalAmount * $this->discountRate;
    }

    private function calculateDiscountRate(): float
    {
        $totalAmount = $this->totalAmount;
        $itemCount = $this->getItemCount();
        $totalQuantity = $this->getTotalQuantity();

        // Bulk discount calculation
        if ($totalAmount >= 100000) {
            return 0.15; // 15% discount
        } elseif ($totalAmount >= 50000) {
            return 0.10; // 10% discount
        } elseif ($totalAmount >= 20000) {
            return 0.07; // 7% discount
        } elseif ($totalAmount >= 10000) {
            return 0.05; // 5% discount
        } elseif ($totalQuantity >= 100) {
            return 0.03; // 3% discount for 100+ items
        } elseif ($itemCount >= 20) {
            return 0.02; // 2% discount for 20+ items
        }

        return 0.0;
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}