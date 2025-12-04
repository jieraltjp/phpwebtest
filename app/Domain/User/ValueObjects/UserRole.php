<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class UserRole extends AbstractValueObject
{
    public const ADMIN = 'admin';
    public const PURCHASE_MANAGER = 'purchase_manager';
    public const SALES_REPRESENTATIVE = 'sales_representative';
    public const CUSTOMER = 'customer';
    public const SUPPLIER = 'supplier';

    private const VALID_ROLES = [
        self::ADMIN,
        self::PURCHASE_MANAGER,
        self::SALES_REPRESENTATIVE,
        self::CUSTOMER,
        self::SUPPLIER,
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function purchaseManager(): self
    {
        return new self(self::PURCHASE_MANAGER);
    }

    public static function salesRepresentative(): self
    {
        return new self(self::SALES_REPRESENTATIVE);
    }

    public static function customer(): self
    {
        return new self(self::CUSTOMER);
    }

    public static function supplier(): self
    {
        return new self(self::SUPPLIER);
    }

    public function isAdmin(): bool
    {
        return $this->value === self::ADMIN;
    }

    public function isPurchaseManager(): bool
    {
        return $this->value === self::PURCHASE_MANAGER;
    }

    public function isSalesRepresentative(): bool
    {
        return $this->value === self::SALES_REPRESENTATIVE;
    }

    public function isCustomer(): bool
    {
        return $this->value === self::CUSTOMER;
    }

    public function isSupplier(): bool
    {
        return $this->value === self::SUPPLIER;
    }

    public function canManageOrders(): bool
    {
        return in_array($this->value, [self::ADMIN, self::PURCHASE_MANAGER, self::SALES_REPRESENTATIVE], true);
    }

    public function canManageProducts(): bool
    {
        return in_array($this->value, [self::ADMIN, self::PURCHASE_MANAGER], true);
    }

    public function canViewReports(): bool
    {
        return in_array($this->value, [self::ADMIN, self::PURCHASE_MANAGER], true);
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::ADMIN => '管理者',
            self::PURCHASE_MANAGER => '采购经理',
            self::SALES_REPRESENTATIVE => '销售代表',
            self::CUSTOMER => '客户',
            self::SUPPLIER => '供应商',
            default => '未知角色',
        };
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        if (!in_array($value, self::VALID_ROLES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid role "%s". Valid roles are: %s',
                $value,
                implode(', ', self::VALID_ROLES)
            ));
        }
    }
}