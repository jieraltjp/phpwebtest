<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class Money extends AbstractValueObject
{
    public const CURRENCY_CNY = 'CNY';
    public const CURRENCY_JPY = 'JPY';
    public const CURRENCY_USD = 'USD';

    private const SUPPORTED_CURRENCIES = [
        self::CURRENCY_CNY => ['symbol' => '¥', 'precision' => 2],
        self::CURRENCY_JPY => ['symbol' => '¥', 'precision' => 0],
        self::CURRENCY_USD => ['symbol' => '$', 'precision' => 2],
    ];

    private int $amount;
    private string $currency;

    public function __construct(int $amount, string $currency = self::CURRENCY_CNY)
    {
        $this->validate($amount, $currency);
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromFloat(float $amount, string $currency = self::CURRENCY_CNY): self
    {
        $precision = self::SUPPORTED_CURRENCIES[$currency]['precision'] ?? 2;
        $intAmount = (int) round($amount * (10 ** $precision));
        return new self($intAmount, $currency);
    }

    public static function fromString(string $amount, string $currency = self::CURRENCY_CNY): self
    {
        $floatAmount = (float) $amount;
        return self::fromFloat($floatAmount, $currency);
    }

    public static function cny(float $amount): self
    {
        return self::fromFloat($amount, self::CURRENCY_CNY);
    }

    public static function jpy(float $amount): self
    {
        return self::fromFloat($amount, self::CURRENCY_JPY);
    }

    public static function usd(float $amount): self
    {
        return self::fromFloat($amount, self::CURRENCY_USD);
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function toFloat(): float
    {
        $precision = self::SUPPORTED_CURRENCIES[$this->currency]['precision'] ?? 2;
        return $this->amount / (10 ** $precision);
    }

    public function format(): string
    {
        $precision = self::SUPPORTED_CURRENCIES[$this->currency]['precision'] ?? 2;
        $symbol = self::SUPPORTED_CURRENCIES[$this->currency]['symbol'] ?? '';
        $formattedAmount = number_format($this->toFloat(), $precision);
        
        return $symbol . $formattedAmount;
    }

    public function add(Money $other): Money
    {
        $this->assertSameCurrency($other);
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): Money
    {
        $this->assertSameCurrency($other);
        $newAmount = $this->amount - $other->amount;
        
        if ($newAmount < 0) {
            throw new \InvalidArgumentException('Resulting amount cannot be negative');
        }
        
        return new self($newAmount, $this->currency);
    }

    public function multiply(float $multiplier): Money
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Multiplier cannot be negative');
        }
        
        $newAmount = (int) round($this->amount * $multiplier);
        return new self($newAmount, $this->currency);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount < $other->amount;
    }

    public function equals(Money $other): bool
    {
        return parent::equals($other) && $this->currency === $other->currency;
    }

    protected function getValue(): mixed
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    private function validate(int $amount, string $currency): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }

        if (!isset(self::SUPPORTED_CURRENCIES[$currency])) {
            throw new \InvalidArgumentException(sprintf(
                'Currency "%s" is not supported. Supported currencies: %s',
                $currency,
                implode(', ', array_keys(self::SUPPORTED_CURRENCIES))
            ));
        }
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Cannot perform operations on different currencies');
        }
    }

    public function getSymbol(): string
    {
        return self::SUPPORTED_CURRENCIES[$this->currency]['symbol'] ?? '';
    }

    public function getPrecision(): int
    {
        return self::SUPPORTED_CURRENCIES[$this->currency]['precision'] ?? 2;
    }
}