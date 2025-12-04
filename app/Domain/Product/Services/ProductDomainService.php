<?php

declare(strict_types=1);

namespace App\Domain\Product\Services;

use App\Domain\Product\Product;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\Money;
use App\Domain\Product\ValueObjects\InventoryStatus;
use App\Domain\Contracts\DomainServiceInterface;
use App\Domain\Contracts\RepositoryInterface;

final class ProductDomainService implements DomainServiceInterface
{
    public function __construct(
        private RepositoryInterface $productRepository
    ) {
    }

    public function getServiceName(): string
    {
        return 'ProductDomainService';
    }

    public function isProductIdUnique(ProductId $productId): bool
    {
        $existingProducts = $this->productRepository->findAll();
        
        foreach ($existingProducts as $product) {
            if ($product instanceof Product && $product->getId()->equals($productId)) {
                return false;
            }
        }

        return true;
    }

    public function calculateOptimalStockLevel(Product $product): int
    {
        $baseStock = 50; // Base stock level
        $categoryMultiplier = $this->getCategoryMultiplier($product->getCategory());
        $priceMultiplier = $this->getPriceMultiplier($product->getPrice());
        
        return (int) ($baseStock * $categoryMultiplier * $priceMultiplier);
    }

    public function shouldReorderProduct(Product $product): bool
    {
        $optimalLevel = $this->calculateOptimalStockLevel($product);
        $currentQuantity = $product->getQuantity();
        
        return $currentQuantity <= ($optimalLevel * 0.2); // Reorder at 20% of optimal level
    }

    public function calculateReorderQuantity(Product $product): int
    {
        $optimalLevel = $this->calculateOptimalStockLevel($product);
        $currentQuantity = $product->getQuantity();
        
        return max($optimalLevel - $currentQuantity, $optimalLevel);
    }

    public function isPriceCompetitive(Product $product, Money $marketPrice): bool
    {
        $productPrice = $product->getPrice();
        
        if ($productPrice->getCurrency() !== $marketPrice->getCurrency()) {
            throw new \InvalidArgumentException('Cannot compare prices in different currencies');
        }

        // Product is competitive if it's within 20% of market price
        $priceDifference = abs($productPrice->toFloat() - $marketPrice->toFloat());
        $percentageDifference = ($priceDifference / $marketPrice->toFloat()) * 100;

        return $percentageDifference <= 20;
    }

    public function suggestPriceAdjustment(Product $product, Money $marketPrice): ?Money
    {
        if ($this->isPriceCompetitive($product, $marketPrice)) {
            return null; // No adjustment needed
        }

        $productPrice = $product->getPrice();
        
        if ($productPrice->isGreaterThan($marketPrice)) {
            // Suggest reducing price to 5% below market
            $suggestedPrice = $marketPrice->multiply(0.95);
        } else {
            // Suggest increasing price but stay competitive
            $suggestedPrice = $marketPrice->multiply(0.95);
        }

        return $suggestedPrice;
    }

    public function calculateProductProfitMargin(Product $product, Money $costPrice): float
    {
        $sellingPrice = $product->getPrice();
        
        if ($sellingPrice->getCurrency() !== $costPrice->getCurrency()) {
            throw new \InvalidArgumentException('Cannot calculate margin with different currencies');
        }

        $margin = $sellingPrice->toFloat() - $costPrice->toFloat();
        return ($margin / $sellingPrice->toFloat()) * 100;
    }

    public function identifySlowMovingProducts(int $daysThreshold = 90): array
    {
        $allProducts = $this->productRepository->findAll();
        $slowMovingProducts = [];
        
        $cutoffDate = new \DateTimeImmutable("-{$daysThreshold} days");
        
        foreach ($allProducts as $product) {
            if ($product instanceof Product && 
                $product->getUpdatedAt() < $cutoffDate &&
                $product->getQuantity() > 0) {
                $slowMovingProducts[] = $product;
            }
        }

        return $slowMovingProducts;
    }

    public function calculateInventoryValue(array $products): Money
    {
        $totalValue = Money::cny(0);
        
        foreach ($products as $product) {
            if ($product instanceof Product) {
                $productValue = $product->getTotalValue();
                if ($productValue->getCurrency() === $totalValue->getCurrency()) {
                    $totalValue = $totalValue->add($productValue);
                }
            }
        }

        return $totalValue;
    }

    public function generateProductReport(array $products): array
    {
        $report = [
            'total_products' => count($products),
            'active_products' => 0,
            'out_of_stock' => 0,
            'low_stock' => 0,
            'total_inventory_value' => Money::cny(0),
            'categories' => [],
            'price_ranges' => [
                'low' => 0,      // < 100 CNY
                'medium' => 0,   // 100-1000 CNY
                'high' => 0,     // > 1000 CNY
            ],
        ];

        foreach ($products as $product) {
            if (!$product instanceof Product) {
                continue;
            }

            if ($product->isActive()) {
                $report['active_products']++;
            }

            if ($product->isOutOfStock()) {
                $report['out_of_stock']++;
            }

            if ($product->isLowStock()) {
                $report['low_stock']++;
            }

            $productValue = $product->getTotalValue();
            if ($productValue->getCurrency() === $report['total_inventory_value']->getCurrency()) {
                $report['total_inventory_value'] = $report['total_inventory_value']->add($productValue);
            }

            // Category analysis
            $category = $product->getCategory() ?: 'Uncategorized';
            $report['categories'][$category] = ($report['categories'][$category] ?? 0) + 1;

            // Price range analysis
            $price = $product->getPrice()->toFloat();
            if ($price < 100) {
                $report['price_ranges']['low']++;
            } elseif ($price <= 1000) {
                $report['price_ranges']['medium']++;
            } else {
                $report['price_ranges']['high']++;
            }
        }

        return $report;
    }

    public function recommendProductsForPromotion(array $products, int $limit = 10): array
    {
        $eligibleProducts = [];
        
        foreach ($products as $product) {
            if (!$product instanceof Product) {
                continue;
            }

            // Include products that are:
            // 1. Active
            // 2. Have sufficient stock
            // 3. Are not discontinued
            // 4. Have been in inventory for a while (slow moving)
            
            if ($product->isActive() && 
                $product->getQuantity() > 10 &&
                !$product->getInventoryStatus()->isDiscontinued()) {
                
                $score = $this->calculatePromotionScore($product);
                $eligibleProducts[] = ['product' => $product, 'score' => $score];
            }
        }

        // Sort by score (highest first) and limit
        usort($eligibleProducts, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($eligibleProducts, 0, $limit);
    }

    private function calculatePromotionScore(Product $product): int
    {
        $score = 0;

        // Higher score for slow-moving products
        $daysSinceUpdate = (new \DateTimeImmutable())->diff($product->getUpdatedAt())->days;
        if ($daysSinceUpdate > 90) {
            $score += 30;
        } elseif ($daysSinceUpdate > 30) {
            $score += 15;
        }

        // Higher score for higher inventory
        if ($product->getQuantity() > 100) {
            $score += 20;
        } elseif ($product->getQuantity() > 50) {
            $score += 10;
        }

        // Higher score for higher-priced items
        $price = $product->getPrice()->toFloat();
        if ($price > 1000) {
            $score += 15;
        } elseif ($price > 500) {
            $score += 10;
        }

        return $score;
    }

    private function getCategoryMultiplier(?string $category): float
    {
        $multipliers = [
            'electronics' => 1.2,
            'furniture' => 0.8,
            'clothing' => 1.5,
            'food' => 2.0,
            'machinery' => 0.5,
        ];

        return $multipliers[$category] ?? 1.0;
    }

    private function getPriceMultiplier(Money $price): float
    {
        $priceValue = $price->toFloat();

        if ($priceValue < 50) {
            return 2.0; // High multiplier for cheap items
        } elseif ($priceValue < 500) {
            return 1.5;
        } elseif ($priceValue < 2000) {
            return 1.0;
        } else {
            return 0.7; // Lower multiplier for expensive items
        }
    }

    public function validateProductCreation(
        ProductId $productId,
        string $name,
        Money $price,
        int $quantity
    ): array {
        $errors = [];

        if (!$this->isProductIdUnique($productId)) {
            $errors[] = 'Product ID already exists';
        }

        if ($price->isZero()) {
            $errors[] = 'Product price cannot be zero';
        }

        if ($quantity < 0) {
            $errors[] = 'Initial quantity cannot be negative';
        }

        if (strlen($name) < 3) {
            $errors[] = 'Product name must be at least 3 characters long';
        }

        return $errors;
    }
}