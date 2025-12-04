<?php

declare(strict_types=1);

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Contracts\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    public function findById(OrderId $id): ?Order;

    public function save(Order $order): void;

    public function delete(Order $order): void;

    /**
     * @return Order[]
     */
    public function findAll(): array;

    /**
     * @return Order[]
     */
    public function findByCustomerId(string $customerId): array;

    /**
     * @return Order[]
     */
    public function findByStatus(OrderStatus $status): array;

    /**
     * @return Order[]
     */
    public function findByCustomerAndStatus(string $customerId, OrderStatus $status): array;

    /**
     * @return Order[]
     */
    public function findActiveOrders(): array;

    /**
     * @return Order[]
     */
    public function findCompletedOrders(): array;

    /**
     * @return Order[]
     */
    public function findCancelledOrders(): array;

    /**
     * @return Order[]
     */
    public function findPendingOrders(): array;

    /**
     * @return Order[]
     */
    public function findProcessingOrders(): array;

    /**
     * @return Order[]
     */
    public function findShippedOrders(): array;

    /**
     * @return Order[]
     */
    public function findDeliveredOrders(): array;

    /**
     * @return Order[]
     */
    public function findOnHoldOrders(): array;

    /**
     * @return Order[]
     */
    public function findBulkOrders(): array;

    /**
     * @return Order[]
     */
    public function findHighValueOrders(): array;

    /**
     * @return Order[]
     */
    public function findOrdersNeedingApproval(): array;

    /**
     * @return Order[]
     */
    public function findOrdersNeedingAttention(): array;

    /**
     * @return Order[]
     */
    public function findRecentlyCreated(int $days = 7): array;

    /**
     * @return Order[]
     */
    public function findRecentlyUpdated(int $days = 7): array;

    /**
     * @return Order[]
     */
    public function findOrdersInDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array;

    /**
     * @return Order[]
     */
    public function findOrdersByDateRangeAndStatus(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        ?OrderStatus $status = null
    ): array;

    /**
     * @return Order[]
     */
    public function findByProduct(string $productId): array;

    /**
     * @return Order[]
     */
    public function findByTrackingNumber(string $trackingNumber): array;

    /**
     * @return Order[]
     */
    public function searchByCustomerEmail(string $email): array;

    /**
     * @return Order[]
     */
    public function searchByKeyword(string $keyword): array;

    /**
     * @return Order[]
     */
    public function findOrdersAboveAmount(float $amount): array;

    /**
     * @return Order[]
     */
    public function findOrdersBelowAmount(float $amount): array;

    /**
     * @return Order[]
     */
    public function findOrdersInAmountRange(float $minAmount, float $maxAmount): array;

    public function countByCustomerId(string $customerId): int;

    public function countByStatus(OrderStatus $status): int;

    public function countActiveOrders(): int;

    public function countCompletedOrders(): int;

    public function countCancelledOrders(): int;

    public function countTotalOrders(): int;

    public function existsById(OrderId $id): bool;

    public function getTotalRevenue(): float;

    public function getRevenueInDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float;

    public function getRevenueByStatus(): array;

    public function getAverageOrderValue(): float;

    public function getCustomerOrderCount(string $customerId): int;

    public function getCustomerTotalSpent(string $customerId): float;

    /**
     * @return array<string, int>
     */
    public function getStatusStatistics(): array;

    /**
     * @return array<string, float>
     */
    public function getMonthlyRevenue(int $months = 12): array;

    /**
     * @return array<string, int>
     */
    public function getTopCustomers(int $limit = 10): array;

    /**
     * @return array<string, int>
     */
    public function getTopProducts(int $limit = 10): array;

    /**
     * @return Order[]
     */
    public function findWithPagination(int $page = 1, int $limit = 20, array $filters = []): array;

    public function countWithFilters(array $filters = []): int;

    /**
     * @return Order[]
     */
    public function findOrdersDelayedInProcessing(int $hours = 24): array;

    /**
     * @return Order[]
     */
    public function findOrdersWithOverdueDelivery(): array;

    public function getProcessingStatistics(): array;

    public function getDeliveryStatistics(): array;
}