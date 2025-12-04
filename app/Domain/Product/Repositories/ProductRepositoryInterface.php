<?php

declare(strict_types=1);

namespace App\Domain\Product\Repositories;

use App\Domain\Product\Product;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\InventoryStatus;
use App\Domain\Contracts\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findById(ProductId $id): ?Product;

    public function save(Product $product): void;

    public function delete(Product $product): void;

    /**
     * @return Product[]
     */
    public function findAll(): array;

    /**
     * @return Product[]
     */
    public function findByCategory(string $category): array;

    /**
     * @return Product[]
     */
    public function findBySupplier(string $supplierId): array;

    /**
     * @return Product[]
     */
    public function findByInventoryStatus(InventoryStatus $status): array;

    /**
     * @return Product[]
     */
    public function findActiveProducts(): array;

    /**
     * @return Product[]
     */
    public function findAvailableProducts(): array;

    /**
     * @return Product[]
     */
    public function findOutOfStockProducts(): array;

    /**
     * @return Product[]
     */
    public function findLowStockProducts(): array;

    /**
     * @return Product[]
     */
    public function findProductsNeedingRestock(): array;

    /**
     * @return Product[]
     */
    public function findDiscontinuedProducts(): array;

    /**
     * @return Product[]
     */
    public function searchByName(string $name): array;

    /**
     * @return Product[]
     */
    public function searchByKeyword(string $keyword): array;

    /**
     * @return Product[]
     */
    public function findByPriceRange(float $minPrice, float $maxPrice, string $currency = 'CNY'): array;

    /**
     * @return Product[]
     */
    public function findByQuantityRange(int $minQuantity, int $maxQuantity): array;

    /**
     * @return Product[]
     */
    public function findRecentlyAdded(int $days = 7): array;

    /**
     * @return Product[]
     */
    public function findRecentlyUpdated(int $days = 7): array;

    /**
     * @return Product[]
     */
    public function findSlowMovingProducts(int $daysThreshold = 90): array;

    /**
     * @return Product[]
     */
    public function findTopSellingProducts(int $limit = 10): array;

    /**
     * @return Product[]
     */
    public function findHighValueProducts(float $minValue = 10000): array;

    public function countByCategory(string $category): int;

    public function countBySupplier(string $supplierId): int;

    public function countByInventoryStatus(InventoryStatus $status): int;

    public function countActiveProducts(): int;

    public function countAvailableProducts(): int;

    public function countOutOfStockProducts(): int;

    public function countLowStockProducts(): int;

    public function countTotalProducts(): int;

    public function existsById(ProductId $id): bool;

    public function getTotalInventoryValue(): float;

    /**
     * @return array<string, int>
     */
    public function getCategoryStatistics(): array;

    /**
     * @return array<string, int>
     */
    public function getInventoryStatusStatistics(): array;

    /**
     * @return Product[]
     */
    public function findWithPagination(int $page = 1, int $limit = 20, array $filters = []): array;

    public function countWithFilters(array $filters = []): int;
}