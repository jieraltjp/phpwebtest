<?php

declare(strict_types=1);

namespace App\Domain\Product;

use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\ProductName;
use App\Domain\Product\ValueObjects\Money;
use App\Domain\Product\ValueObjects\InventoryStatus;
use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\InventoryChanged;
use App\Domain\Abstractions\AbstractAggregateRoot;

final class Product extends AbstractAggregateRoot
{
    private ProductId $id;
    private ProductName $name;
    private string $description;
    private Money $price;
    private int $quantity;
    private InventoryStatus $inventoryStatus;
    private ?string $supplierId;
    private ?string $category;
    private array $specifications;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;
    private bool $isActive;

    private function __construct(
        ProductId $id,
        ProductName $name,
        string $description,
        Money $price,
        int $quantity,
        ?string $supplierId = null,
        ?string $category = null,
        array $specifications = []
    ) {
        parent::__construct($id->toString());
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->inventoryStatus = InventoryStatus::determineFromQuantity($quantity);
        $this->supplierId = $supplierId;
        $this->category = $category;
        $this->specifications = $specifications;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
        $this->isActive = true;
    }

    public static function create(
        ProductId $id,
        ProductName $name,
        string $description,
        Money $price,
        int $quantity,
        ?string $supplierId = null,
        ?string $category = null,
        array $specifications = []
    ): self {
        $product = new self(
            $id,
            $name,
            $description,
            $price,
            $quantity,
            $supplierId,
            $category,
            $specifications
        );

        $product->recordDomainEvent(
            ProductCreated::create(
                $id,
                $name->toString(),
                $description,
                $price->toFloat(),
                $price->getCurrency()
            )
        );

        return $product;
    }

    public static function createExisting(
        ProductId $id,
        ProductName $name,
        string $description,
        Money $price,
        int $quantity,
        InventoryStatus $inventoryStatus,
        ?string $supplierId,
        ?string $category,
        array $specifications,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
        bool $isActive = true
    ): self {
        $product = new self(
            $id,
            $name,
            $description,
            $price,
            $quantity,
            $supplierId,
            $category,
            $specifications
        );
        $product->inventoryStatus = $inventoryStatus;
        $product->createdAt = $createdAt;
        $product->updatedAt = $updatedAt;
        $product->isActive = $isActive;

        return $product;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getName(): ProductName
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getInventoryStatus(): InventoryStatus
    {
        return $this->inventoryStatus;
    }

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getSpecifications(): array
    {
        return $this->specifications;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function updateName(ProductName $newName): void
    {
        if ($this->name->equals($newName)) {
            return;
        }

        $this->name = $newName;
        $this->markAsUpdated();
    }

    public function updateDescription(string $newDescription): void
    {
        if ($this->description === $newDescription) {
            return;
        }

        $this->description = $newDescription;
        $this->markAsUpdated();
    }

    public function updatePrice(Money $newPrice): void
    {
        if ($this->price->equals($newPrice)) {
            return;
        }

        $oldPrice = $this->price;
        $this->price = $newPrice;
        $this->markAsUpdated();

        // Could emit price change event here if needed
    }

    public function updateQuantity(int $newQuantity, string $reason, ?string $referenceId = null): void
    {
        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative');
        }

        if ($this->quantity === $newQuantity) {
            return;
        }

        $oldQuantity = $this->quantity;
        $this->quantity = $newQuantity;
        $this->inventoryStatus = InventoryStatus::determineFromQuantity($newQuantity);
        $this->markAsUpdated();

        $this->recordDomainEvent(
            InventoryChanged::create(
                $this->id,
                $oldQuantity,
                $newQuantity,
                $reason,
                $referenceId
            )
        );
    }

    public function increaseQuantity(int $amount, string $reason, ?string $referenceId = null): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Increase amount must be positive');
        }

        $this->updateQuantity(
            $this->quantity + $amount,
            $reason,
            $referenceId
        );
    }

    public function decreaseQuantity(int $amount, string $reason, ?string $referenceId = null): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Decrease amount must be positive');
        }

        $newQuantity = $this->quantity - $amount;
        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Insufficient inventory');
        }

        $this->updateQuantity($newQuantity, $reason, $referenceId);
    }

    public function reserveQuantity(int $amount, string $orderId): bool
    {
        if (!$this->canReserveQuantity($amount)) {
            return false;
        }

        $this->decreaseQuantity($amount, 'order_reservation', $orderId);
        return true;
    }

    public function releaseReservedQuantity(int $amount, string $orderId): void
    {
        $this->increaseQuantity($amount, 'order_cancellation', $orderId);
    }

    public function updateCategory(?string $newCategory): void
    {
        if ($this->category === $newCategory) {
            return;
        }

        $this->category = $newCategory;
        $this->markAsUpdated();
    }

    public function updateSupplierId(?string $newSupplierId): void
    {
        if ($this->supplierId === $newSupplierId) {
            return;
        }

        $this->supplierId = $newSupplierId;
        $this->markAsUpdated();
    }

    public function updateSpecifications(array $newSpecifications): void
    {
        if ($this->specifications === $newSpecifications) {
            return;
        }

        $this->specifications = $newSpecifications;
        $this->markAsUpdated();
    }

    public function activate(): void
    {
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->markAsUpdated();
    }

    public function deactivate(): void
    {
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->markAsUpdated();
    }

    public function discontinue(): void
    {
        $this->inventoryStatus = InventoryStatus::discontinued();
        $this->isActive = false;
        $this->markAsUpdated();
    }

    public function isAvailable(): bool
    {
        return $this->isActive && $this->inventoryStatus->isAvailable();
    }

    public function canBeOrdered(): bool
    {
        return $this->isActive && $this->inventoryStatus->canBeOrdered();
    }

    public function canReserveQuantity(int $amount): bool
    {
        return $this->canBeOrdered() && $this->quantity >= $amount;
    }

    public function needsRestock(): bool
    {
        return $this->inventoryStatus->needsReorder();
    }

    public function isLowStock(): bool
    {
        return $this->inventoryStatus->isLowStock();
    }

    public function isOutOfStock(): bool
    {
        return $this->inventoryStatus->isOutOfStock();
    }

    public function getTotalValue(): Money
    {
        return $this->price->multiply($this->quantity);
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}