<?php

declare(strict_types=1);

namespace App\Domain\Order;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\OrderItem;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\OrderStatusChanged;
use App\Domain\Abstractions\AbstractAggregateRoot;

final class Order extends AbstractAggregateRoot
{
    private OrderId $id;
    private string $customerId;
    private string $customerEmail;
    private array $items;
    private float $totalAmount;
    private string $currency;
    private OrderStatus $status;
    private ?string $shippingAddress;
    private ?string $billingAddress;
    private ?string $notes;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $confirmedAt;
    private ?\DateTimeImmutable $shippedAt;
    private ?\DateTimeImmutable $deliveredAt;
    private ?string $trackingNumber;

    private function __construct(
        OrderId $id,
        string $customerId,
        string $customerEmail,
        array $items,
        float $totalAmount,
        string $currency = 'CNY'
    ) {
        parent::__construct($id->toString());
        $this->id = $id;
        $this->customerId = $customerId;
        $this->customerEmail = $customerEmail;
        $this->items = $items;
        $this->totalAmount = $totalAmount;
        $this->currency = $currency;
        $this->status = OrderStatus::pending();
        $this->shippingAddress = null;
        $this->billingAddress = null;
        $this->notes = null;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
        $this->confirmedAt = null;
        $this->shippedAt = null;
        $this->deliveredAt = null;
        $this->trackingNumber = null;
    }

    public static function create(
        OrderId $id,
        string $customerId,
        string $customerEmail,
        array $items,
        string $currency = 'CNY'
    ): self {
        if (empty($items)) {
            throw new \InvalidArgumentException('Order must contain at least one item');
        }

        $totalAmount = array_reduce($items, function ($sum, OrderItem $item) {
            return $sum + $item->getTotalPrice();
        }, 0);

        $order = new self($id, $customerId, $customerEmail, $items, $totalAmount, $currency);

        $order->recordDomainEvent(
            OrderCreated::create(
                $id,
                $customerId,
                $items,
                $totalAmount,
                $currency,
                $customerEmail
            )
        );

        return $order;
    }

    public static function createExisting(
        OrderId $id,
        string $customerId,
        string $customerEmail,
        array $items,
        float $totalAmount,
        string $currency,
        OrderStatus $status,
        ?string $shippingAddress,
        ?string $billingAddress,
        ?string $notes,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
        ?\DateTimeImmutable $confirmedAt = null,
        ?\DateTimeImmutable $shippedAt = null,
        ?\DateTimeImmutable $deliveredAt = null,
        ?string $trackingNumber = null
    ): self {
        $order = new self($id, $customerId, $customerEmail, $items, $totalAmount, $currency);
        $order->status = $status;
        $order->shippingAddress = $shippingAddress;
        $order->billingAddress = $billingAddress;
        $order->notes = $notes;
        $order->createdAt = $createdAt;
        $order->updatedAt = $updatedAt;
        $order->confirmedAt = $confirmedAt;
        $order->shippedAt = $shippedAt;
        $order->deliveredAt = $deliveredAt;
        $order->trackingNumber = $trackingNumber;

        return $order;
    }

    public function getId(): OrderId
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

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
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

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function getShippedAt(): ?\DateTimeImmutable
    {
        return $this->shippedAt;
    }

    public function getDeliveredAt(): ?\DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
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

    public function changeStatus(OrderStatus $newStatus, ?string $reason = null, ?string $changedBy = null): void
    {
        if ($this->status->equals($newStatus)) {
            return;
        }

        if (!$this->status->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot transition from %s to %s',
                $this->status->getValue(),
                $newStatus->getValue()
            ));
        }

        $oldStatus = $this->status->getValue();
        $this->status = $newStatus;
        $this->markAsUpdated();

        // Update timestamps based on status
        match ($newStatus->getValue()) {
            'confirmed' => $this->confirmedAt = new \DateTimeImmutable(),
            'shipped' => $this->shippedAt = new \DateTimeImmutable(),
            'delivered' => $this->deliveredAt = new \DateTimeImmutable(),
            default => null,
        };

        $this->recordDomainEvent(
            OrderStatusChanged::create(
                $this->id,
                $oldStatus,
                $newStatus->getValue(),
                $reason,
                $changedBy
            )
        );
    }

    public function confirm(?string $confirmedBy = null): void
    {
        $this->changeStatus(OrderStatus::confirmed(), 'Order confirmed', $confirmedBy);
    }

    public function startProcessing(?string $processedBy = null): void
    {
        $this->changeStatus(OrderStatus::processing(), 'Processing started', $processedBy);
    }

    public function ship(string $trackingNumber, ?string $shippedBy = null): void
    {
        $this->trackingNumber = $trackingNumber;
        $this->changeStatus(OrderStatus::shipped(), 'Order shipped', $shippedBy);
    }

    public function deliver(?string $deliveredBy = null): void
    {
        $this->changeStatus(OrderStatus::delivered(), 'Order delivered', $deliveredBy);
    }

    public function cancel(?string $reason = null, ?string $cancelledBy = null): void
    {
        if (!$this->status->canBeCancelled()) {
            throw new \InvalidArgumentException('Order cannot be cancelled in current status');
        }

        $this->changeStatus(OrderStatus::cancelled(), $reason ?: 'Order cancelled', $cancelledBy);
    }

    public function refund(?string $reason = null, ?string $refundedBy = null): void
    {
        if (!$this->status->canBeRefunded()) {
            throw new \InvalidArgumentException('Order cannot be refunded in current status');
        }

        $this->changeStatus(OrderStatus::refunded(), $reason ?: 'Order refunded', $refundedBy);
    }

    public function putOnHold(?string $reason = null, ?string $heldBy = null): void
    {
        $this->changeStatus(OrderStatus::onHold(), $reason ?: 'Order put on hold', $heldBy);
    }

    public function resumeFromHold(?string $resumedBy = null): void
    {
        if (!$this->status->isOnHold()) {
            throw new \InvalidArgumentException('Order is not on hold');
        }

        $this->changeStatus(OrderStatus::processing(), 'Resumed from hold', $resumedBy);
    }

    public function updateShippingAddress(string $address): void
    {
        if (!$this->status->canBeModified()) {
            throw new \InvalidArgumentException('Cannot modify shipping address in current status');
        }

        $this->shippingAddress = $address;
        $this->markAsUpdated();
    }

    public function updateBillingAddress(string $address): void
    {
        if (!$this->status->canBeModified()) {
            throw new \InvalidArgumentException('Cannot modify billing address in current status');
        }

        $this->billingAddress = $address;
        $this->markAsUpdated();
    }

    public function updateNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->markAsUpdated();
    }

    public function addItem(OrderItem $item): void
    {
        if (!$this->status->canBeModified()) {
            throw new \InvalidArgumentException('Cannot add items to order in current status');
        }

        // Check if item already exists
        foreach ($this->items as $existingItem) {
            if ($existingItem->isSameProduct($item->getProductId())) {
                throw new \InvalidArgumentException('Product already exists in order');
            }
        }

        $this->items[] = $item;
        $this->recalculateTotal();
        $this->markAsUpdated();
    }

    public function removeItem(string $productId): void
    {
        if (!$this->status->canBeModified()) {
            throw new \InvalidArgumentException('Cannot remove items from order in current status');
        }

        $this->items = array_filter($this->items, function (OrderItem $item) use ($productId) {
            return !$item->isSameProduct($productId);
        });

        if (empty($this->items)) {
            throw new \InvalidArgumentException('Order must contain at least one item');
        }

        $this->recalculateTotal();
        $this->markAsUpdated();
    }

    public function updateItemQuantity(string $productId, int $newQuantity): void
    {
        if (!$this->status->canBeModified()) {
            throw new \InvalidArgumentException('Cannot modify items in current status');
        }

        foreach ($this->items as $index => $item) {
            if ($item->isSameProduct($productId)) {
                $this->items[$index] = $item->updateQuantity($newQuantity);
                $this->recalculateTotal();
                $this->markAsUpdated();
                return;
            }
        }

        throw new \InvalidArgumentException('Product not found in order');
    }

    public function isBulkOrder(): bool
    {
        return $this->getTotalAmount() >= 50000 || $this->getItemCount() >= 20;
    }

    public function isHighValueOrder(): bool
    {
        return $this->getTotalAmount() >= 100000;
    }

    public function requiresSpecialHandling(): bool
    {
        return $this->isBulkOrder() || $this->isHighValueOrder();
    }

    public function canBeModified(): bool
    {
        return $this->status->canBeModified();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isTerminated(): bool
    {
        return $this->status->isTerminated();
    }

    public function getProcessingTime(): ?\DateInterval
    {
        if (!$this->deliveredAt || !$this->createdAt) {
            return null;
        }

        return $this->createdAt->diff($this->deliveredAt);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'customer_id' => $this->customerId,
            'customer_email' => $this->customerEmail,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'status' => $this->status->getValue(),
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'confirmed_at' => $this->confirmedAt?->format('Y-m-d H:i:s'),
            'shipped_at' => $this->shippedAt?->format('Y-m-d H:i:s'),
            'delivered_at' => $this->deliveredAt?->format('Y-m-d H:i:s'),
            'tracking_number' => $this->trackingNumber,
            'item_count' => $this->getItemCount(),
            'total_quantity' => $this->getTotalQuantity(),
            'is_bulk_order' => $this->isBulkOrder(),
            'is_high_value_order' => $this->isHighValueOrder(),
            'requires_special_handling' => $this->requiresSpecialHandling(),
        ];
    }

    private function recalculateTotal(): void
    {
        $this->totalAmount = array_reduce($this->items, function ($sum, OrderItem $item) {
            return $sum + $item->getTotalPrice();
        }, 0);
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}