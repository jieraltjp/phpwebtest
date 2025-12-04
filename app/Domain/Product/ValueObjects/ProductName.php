<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use App\Domain\Abstractions\AbstractValueObject;

final class ProductName extends AbstractValueObject
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = trim($value);
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function getShortName(int $maxLength = 50): string
    {
        if (strlen($this->value) <= $maxLength) {
            return $this->value;
        }

        return substr($this->value, 0, $maxLength - 3) . '...';
    }

    public function containsKeyword(string $keyword): bool
    {
        return stripos($this->value, $keyword) !== false;
    }

    public function getWordCount(): int
    {
        return str_word_count($this->value);
    }

    protected function getValue(): mixed
    {
        return $this->value;
    }

    private function validate(string $value): void
    {
        $trimmed = trim($value);
        
        if (empty($trimmed)) {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        if (strlen($trimmed) < 3) {
            throw new \InvalidArgumentException('Product name must be at least 3 characters long');
        }

        if (strlen($trimmed) > 255) {
            throw new \InvalidArgumentException('Product name cannot exceed 255 characters');
        }

        // Check for inappropriate content (basic validation)
        $inappropriateWords = ['spam', 'fake', 'counterfeit', 'illegal'];
        foreach ($inappropriateWords as $word) {
            if (stripos($trimmed, $word) !== false) {
                throw new \InvalidArgumentException('Product name contains inappropriate content');
            }
        }
    }
}