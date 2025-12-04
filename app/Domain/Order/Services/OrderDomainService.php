<?php

declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Domain\Order\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\OrderItem;
use App\Domain\Contracts\DomainServiceInterface;
use App\Domain\Contracts\RepositoryInterface;

final class OrderDomainService implements DomainServiceInterface
{
    public function __construct(
        private RepositoryInterface $orderRepository,
        private RepositoryInterface $productRepository
    ) {
    }

    public function getServiceName(): string
    {
        return 'OrderDomainService';
    }

    public function isOrderIdUnique(OrderId $orderId): bool
    {
        $existingOrders = $this->orderRepository->findAll();
        
        foreach ($existingOrders as $order) {
            if ($order instanceof Order && $order->getId()->equals($orderId)) {
                return false;
            }
        }

        return true;
    }

    public function canCreateOrder(string $customerId, array $items): array
    {
        $errors = [];

        if (empty($customerId)) {
            $errors[] = 'Customer ID is required';
        }

        if (empty($items)) {
            $errors[] = 'Order must contain at least one item';
            return $errors;
        }

        // Validate each item
        foreach ($items as $item) {
            if (!$item instanceof OrderItem) {
                $errors[] = 'All items must be OrderItem instances';
                continue;
            }

            // Check product availability (would need product repository)
            $product = $this->productRepository->findById($item->getProductId());
            if (!$product) {
                $errors[] = "Product {$item->getProductId()} not found";
            }
        }

        // Check for duplicate products
        $productIds = array_map(fn($item) => $item->getProductId(), $items);
        if (count($productIds) !== count(array_unique($productIds))) {
            $errors[] = 'Order cannot contain duplicate products';
        }

        return $errors;
    }

    public function calculateOrderTotal(array $items): float
    {
        return array_reduce($items, function ($sum, OrderItem $item) {
            return $sum + $item->getTotalPrice();
        }, 0);
    }

    public function applyBulkDiscount(float $totalAmount, int $itemCount): float
    {
        $discount = 0.0;

        // Bulk discount rules
        if ($totalAmount >= 100000) {
            $discount = 0.15; // 15% discount for orders >= 100,000
        } elseif ($totalAmount >= 50000) {
            $discount = 0.10; // 10% discount for orders >= 50,000
        } elseif ($totalAmount >= 20000) {
            $discount = 0.07; // 7% discount for orders >= 20,000
        } elseif ($totalAmount >= 10000) {
            $discount = 0.05; // 5% discount for orders >= 10,000
        } elseif ($itemCount >= 50) {
            $discount = 0.03; // 3% discount for 50+ items
        } elseif ($itemCount >= 20) {
            $discount = 0.02; // 2% discount for 20+ items
        }

        return $totalAmount * (1 - $discount);
    }

    public function calculateShippingCost(float $totalAmount, int $totalQuantity, string $region = 'domestic'): float
    {
        // Free shipping for high-value orders
        if ($totalAmount >= 50000) {
            return 0.0;
        }

        $baseShipping = match ($region) {
            'domestic' => 500.0, // ¥500 base shipping
            'international' => 2000.0, // ¥2000 base shipping
            'express' => 1500.0, // ¥1500 express shipping
            default => 500.0,
        };

        // Additional cost based on quantity
        $quantitySurcharge = max(0, ($totalQuantity - 10) * 50); // ¥50 per item after 10

        // Weight-based calculation (simplified)
        $weightSurcharge = $totalQuantity * 20; // ¥20 per item (estimated weight)

        return $baseShipping + $quantitySurcharge + $weightSurcharge;
    }

    public function estimateDeliveryTime(string $region, int $totalQuantity): \DateInterval
    {
        $baseDays = match ($region) {
            'domestic' => 3,
            'international' => 14,
            'express' => 1,
            default => 7,
        };

        // Additional time for bulk orders
        $additionalDays = $totalQuantity > 100 ? 2 : ($totalQuantity > 50 ? 1 : 0);

        return new \DateInterval("P" . ($baseDays + $additionalDays) . "D");
    }

    public function shouldRequireApproval(Order $order): bool
    {
        // High-value orders require approval
        if ($order->isHighValueOrder()) {
            return true;
        }

        // Bulk orders require approval
        if ($order->isBulkOrder()) {
            return true;
        }

        // Orders with certain items might require approval
        foreach ($order->getItems() as $item) {
            if ($item instanceof OrderItem && $item->isHighValue()) {
                return true;
            }
        }

        return false;
    }

    public function validateOrderModification(Order $order, array $changes): array
    {
        $errors = [];

        if (!$order->canBeModified()) {
            $errors[] = 'Order cannot be modified in current status';
            return $errors;
        }

        // Validate item additions
        if (isset($changes['add_items'])) {
            foreach ($changes['add_items'] as $item) {
                if (!$item instanceof OrderItem) {
                    $errors[] = 'Invalid item format for addition';
                    continue;
                }

                // Check for duplicates
                foreach ($order->getItems() as $existingItem) {
                    if ($existingItem->isSameProduct($item->getProductId())) {
                        $errors[] = "Product {$item->getProductId()} already exists in order";
                    }
                }
            }
        }

        // Validate item removals
        if (isset($changes['remove_items'])) {
            $currentItemCount = count($order->getItems());
            $removeCount = count($changes['remove_items']);

            if ($removeCount >= $currentItemCount) {
                $errors[] = 'Order must contain at least one item';
            }
        }

        return $errors;
    }

    public function generateOrderReport(array $orders): array
    {
        $report = [
            'total_orders' => count($orders),
            'total_revenue' => 0,
            'average_order_value' => 0,
            'status_distribution' => [],
            'bulk_orders' => 0,
            'high_value_orders' => 0,
            'monthly_stats' => [],
            'top_customers' => [],
            'top_products' => [],
        ];

        $monthlyRevenue = [];
        $customerOrders = [];
        $productSales = [];

        foreach ($orders as $order) {
            if (!$order instanceof Order) {
                continue;
            }

            $total = $order->getTotalAmount();
            $report['total_revenue'] += $total;

            // Status distribution
            $status = $order->getStatus()->getValue();
            $report['status_distribution'][$status] = ($report['status_distribution'][$status] ?? 0) + 1;

            // Bulk and high value orders
            if ($order->isBulkOrder()) {
                $report['bulk_orders']++;
            }
            if ($order->isHighValueOrder()) {
                $report['high_value_orders']++;
            }

            // Monthly statistics
            $month = $order->getCreatedAt()->format('Y-m');
            $monthlyRevenue[$month] = ($monthlyRevenue[$month] ?? 0) + $total;

            // Customer statistics
            $customerId = $order->getCustomerId();
            $customerOrders[$customerId] = ($customerOrders[$customerId] ?? 0) + 1;

            // Product statistics
            foreach ($order->getItems() as $item) {
                if ($item instanceof OrderItem) {
                    $productId = $item->getProductId();
                    $productSales[$productId] = ($productSales[$productId] ?? 0) + $item->getQuantity();
                }
            }
        }

        $report['average_order_value'] = count($orders) > 0 ? $report['total_revenue'] / count($orders) : 0;

        // Sort and limit top customers and products
        arsort($customerOrders);
        arsort($productSales);
        $report['top_customers'] = array_slice($customerOrders, 0, 10, true);
        $report['top_products'] = array_slice($productSales, 0, 10, true);
        $report['monthly_stats'] = $monthlyRevenue;

        return $report;
    }

    public function identifyAtRiskOrders(array $orders): array
    {
        $atRiskOrders = [];
        $cutoffDate = new \DateTimeImmutable('-30 days');

        foreach ($orders as $order) {
            if (!$order instanceof Order) {
                continue;
            }

            // Orders pending for more than 30 days
            if ($order->getStatus()->isPending() && $order->getCreatedAt() < $cutoffDate) {
                $atRiskOrders[] = [
                    'order' => $order,
                    'risk' => 'pending_too_long',
                    'reason' => 'Order has been pending for over 30 days',
                ];
            }

            // High-value orders not confirmed
            if ($order->isHighValueOrder() && $order->getStatus()->isPending()) {
                $atRiskOrders[] = [
                    'order' => $order,
                    'risk' => 'high_value_pending',
                    'reason' => 'High-value order requires attention',
                ];
            }

            // Orders on hold for more than 7 days
            if ($order->getStatus()->isOnHold()) {
                $holdCutoff = new \DateTimeImmutable('-7 days');
                if ($order->getUpdatedAt() && $order->getUpdatedAt() < $holdCutoff) {
                    $atRiskOrders[] = [
                        'order' => $order,
                        'risk' => 'on_hold_too_long',
                        'reason' => 'Order has been on hold for over 7 days',
                    ];
                }
            }
        }

        return $atRiskOrders;
    }

    public function calculateOrderMetrics(array $orders, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $filteredOrders = array_filter($orders, function ($order) use ($startDate, $endDate) {
            if (!$order instanceof Order) {
                return false;
            }
            $orderDate = $order->getCreatedAt();
            return $orderDate >= $startDate && $orderDate <= $endDate;
        });

        $metrics = [
            'total_orders' => count($filteredOrders),
            'total_revenue' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
            'average_processing_time' => 0,
            'customer_retention_rate' => 0,
        ];

        $processingTimes = [];
        $customers = [];

        foreach ($filteredOrders as $order) {
            $metrics['total_revenue'] += $order->getTotalAmount();

            if ($order->getStatus()->isCompleted()) {
                $metrics['completed_orders']++;
            }
            if ($order->getStatus()->isTerminated()) {
                $metrics['cancelled_orders']++;
            }

            $processingTime = $order->getProcessingTime();
            if ($processingTime) {
                $processingTimes[] = $processingTime->days;
            }

            $customers[] = $order->getCustomerId();
        }

        // Calculate average processing time
        if (!empty($processingTimes)) {
            $metrics['average_processing_time'] = array_sum($processingTimes) / count($processingTimes);
        }

        // Calculate customer retention rate (simplified)
        $uniqueCustomers = array_unique($customers);
        $metrics['customer_retention_rate'] = count($uniqueCustomers) > 0 ? 
            (count($filteredOrders) / count($uniqueCustomers)) * 100 : 0;

        return $metrics;
    }

    public function recommendShippingMethod(Order $order): string
    {
        $totalAmount = $order->getTotalAmount();
        $totalQuantity = $order->getTotalQuantity();
        $isHighValue = $order->isHighValueOrder();
        $isBulk = $order->isBulkOrder();

        // High-value and bulk orders should use express shipping
        if ($isHighValue || $isBulk) {
            return 'express';
        }

        // Orders over 20,000 should use express
        if ($totalAmount >= 20000) {
            return 'express';
        }

        // Large quantity orders should use express
        if ($totalQuantity >= 100) {
            return 'express';
        }

        // Default to domestic shipping
        return 'domestic';
    }
}