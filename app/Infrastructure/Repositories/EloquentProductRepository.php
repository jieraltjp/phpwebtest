<?php

declare(strict_types=1);

namespace App\Infrastructure\Repositories;

use App\Domain\Product\Product;
use App\Domain\Product\ValueObjects\ProductId;
use App\Domain\Product\ValueObjects\ProductName;
use App\Domain\Product\ValueObjects\Money;
use App\Domain\Product\ValueObjects\InventoryStatus;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Models\Product as ProductModel;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    public function findById(ProductId $id): ?Product
    {
        $productModel = ProductModel::find($id->toString());
        
        if (!$productModel) {
            return null;
        }

        return $this->mapToDomainEntity($productModel);
    }

    public function save(Product $product): void
    {
        $productModel = ProductModel::find($product->getId()->toString());
        
        if (!$productModel) {
            $productModel = new ProductModel();
            $productModel->id = $product->getId()->toString();
        }

        $productModel->name = $product->getName()->toString();
        $productModel->description = $product->getDescription();
        $productModel->price = $product->getPrice()->toFloat();
        $productModel->currency = $product->getPrice()->getCurrency();
        $productModel->quantity = $product->getQuantity();
        $productModel->inventory_status = $product->getInventoryStatus()->getValue();
        $productModel->supplier_id = $product->getSupplierId();
        $productModel->category = $product->getCategory();
        $productModel->specifications = json_encode($product->getSpecifications());
        $productModel->created_at = $product->getCreatedAt();
        $productModel->updated_at = $product->getUpdatedAt();
        $productModel->is_active = $product->isActive();
        
        $productModel->save();
    }

    public function delete(Product $product): void
    {
        ProductModel::destroy($product->getId()->toString());
    }

    public function findAll(): array
    {
        $productModels = ProductModel::all();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findByCategory(string $category): array
    {
        $productModels = ProductModel::where('category', $category)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findBySupplier(string $supplierId): array
    {
        $productModels = ProductModel::where('supplier_id', $supplierId)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findByInventoryStatus(InventoryStatus $status): array
    {
        $productModels = ProductModel::where('inventory_status', $status->getValue())->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findActiveProducts(): array
    {
        $productModels = ProductModel::where('is_active', true)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findAvailableProducts(): array
    {
        $availableStatuses = ['in_stock', 'low_stock', 'on_order'];
        $productModels = ProductModel::where('is_active', true)
                                    ->whereIn('inventory_status', $availableStatuses)
                                    ->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findOutOfStockProducts(): array
    {
        return $this->findByInventoryStatus(InventoryStatus::outOfStock());
    }

    public function findLowStockProducts(): array
    {
        return $this->findByInventoryStatus(InventoryStatus::lowStock());
    }

    public function findProductsNeedingRestock(): array
    {
        $restockStatuses = ['out_of_stock', 'low_stock'];
        $productModels = ProductModel::whereIn('inventory_status', $restockStatuses)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findDiscontinuedProducts(): array
    {
        return $this->findByInventoryStatus(InventoryStatus::discontinued());
    }

    public function searchByName(string $name): array
    {
        $productModels = ProductModel::where('name', 'like', "%{$name}%")->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function searchByKeyword(string $keyword): array
    {
        $productModels = ProductModel::where('name', 'like', "%{$keyword}%")
                                    ->orWhere('description', 'like', "%{$keyword}%")
                                    ->orWhere('category', 'like', "%{$keyword}%")
                                    ->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findByPriceRange(float $minPrice, float $maxPrice, string $currency = 'CNY'): array
    {
        $productModels = ProductModel::where('currency', $currency)
                                    ->whereBetween('price', [$minPrice, $maxPrice])
                                    ->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findByQuantityRange(int $minQuantity, int $maxQuantity): array
    {
        $productModels = ProductModel::whereBetween('quantity', [$minQuantity, $maxQuantity])->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findRecentlyAdded(int $days = 7): array
    {
        $cutoffDate = now()->subDays($days);
        $productModels = ProductModel::where('created_at', '>=', $cutoffDate)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findRecentlyUpdated(int $days = 7): array
    {
        $cutoffDate = now()->subDays($days);
        $productModels = ProductModel::where('updated_at', '>=', $cutoffDate)->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findSlowMovingProducts(int $daysThreshold = 90): array
    {
        $cutoffDate = now()->subDays($daysThreshold);
        $productModels = ProductModel::where('updated_at', '<', $cutoffDate)
                                    ->where('quantity', '>', 0)
                                    ->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function findTopSellingProducts(int $limit = 10): array
    {
        // This would require order item data, simplified for now
        return $this->findActiveProducts();
    }

    public function findHighValueProducts(float $minValue = 10000): array
    {
        $productModels = ProductModel::whereRaw('(price * quantity) >= ?', [$minValue])->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function countByCategory(string $category): int
    {
        return ProductModel::where('category', $category)->count();
    }

    public function countBySupplier(string $supplierId): int
    {
        return ProductModel::where('supplier_id', $supplierId)->count();
    }

    public function countByInventoryStatus(InventoryStatus $status): int
    {
        return ProductModel::where('inventory_status', $status->getValue())->count();
    }

    public function countActiveProducts(): int
    {
        return ProductModel::where('is_active', true)->count();
    }

    public function countAvailableProducts(): int
    {
        $availableStatuses = ['in_stock', 'low_stock', 'on_order'];
        return ProductModel::where('is_active', true)
                          ->whereIn('inventory_status', $availableStatuses)
                          ->count();
    }

    public function countOutOfStockProducts(): int
    {
        return $this->countByInventoryStatus(InventoryStatus::outOfStock());
    }

    public function countLowStockProducts(): int
    {
        return $this->countByInventoryStatus(InventoryStatus::lowStock());
    }

    public function countTotalProducts(): int
    {
        return ProductModel::count();
    }

    public function existsById(ProductId $id): bool
    {
        return ProductModel::where('id', $id->toString())->exists();
    }

    public function getTotalInventoryValue(): float
    {
        return ProductModel::selectRaw('SUM(price * quantity) as total_value')
                          ->first()
                          ->total_value ?? 0;
    }

    public function getCategoryStatistics(): array
    {
        return ProductModel::selectRaw('category, COUNT(*) as count')
                          ->groupBy('category')
                          ->pluck('count', 'category')
                          ->toArray();
    }

    public function getInventoryStatusStatistics(): array
    {
        return ProductModel::selectRaw('inventory_status, COUNT(*) as count')
                          ->groupBy('inventory_status')
                          ->pluck('count', 'inventory_status')
                          ->toArray();
    }

    public function findWithPagination(int $page = 1, int $limit = 20, array $filters = []): array
    {
        $query = ProductModel::query();

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (isset($filters['inventory_status'])) {
            $query->where('inventory_status', $filters['inventory_status']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $productModels = $query->skip(($page - 1) * $limit)
                               ->take($limit)
                               ->get();
        
        return $productModels->map(function ($productModel) {
            return $this->mapToDomainEntity($productModel);
        })->toArray();
    }

    public function countWithFilters(array $filters = []): int
    {
        $query = ProductModel::query();

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (isset($filters['inventory_status'])) {
            $query->where('inventory_status', $filters['inventory_status']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->count();
    }

    private function mapToDomainEntity(ProductModel $productModel): Product
    {
        $specifications = json_decode($productModel->specifications ?: '[]', true);
        
        return Product::createExisting(
            ProductId::fromString($productModel->id),
            ProductName::fromString($productModel->name),
            $productModel->description,
            Money::fromFloat($productModel->price, $productModel->currency),
            $productModel->quantity,
            InventoryStatus::fromString($productModel->inventory_status),
            $productModel->supplier_id,
            $productModel->category,
            $specifications,
            $productModel->created_at,
            $productModel->updated_at,
            $productModel->is_active
        );
    }
}