<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Order\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\OrderItem;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\Services\OrderDomainService;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Application\DTOs\CreateOrderDTO;
use App\Application\DTOs\UpdateOrderDTO;
use App\Application\DTOs\OrderDTO;

final class OrderApplicationService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private OrderDomainService $orderDomainService,
        private EventDispatcherService $eventDispatcher
    ) {
    }

    public function createOrder(CreateOrderDTO $dto): OrderDTO
    {
        // Validate order creation
        $items = [];
        foreach ($dto->items as $itemData) {
            $item = OrderItem::create(
                $itemData['product_id'],
                $itemData['product_name'],
                $itemData['quantity'],
                $itemData['unit_price'],
                $itemData['currency'] ?? 'CNY',
                $itemData['specifications'] ?? []
            );
            $items[] = $item;
        }

        $validationErrors = $this->orderDomainService->canCreateOrder($dto->customerId, $items);
        if (!empty($validationErrors)) {
            throw new \InvalidArgumentException(implode(', ', $validationErrors));
        }

        // Generate order ID
        $orderId = OrderId::generate();

        // Create order entity
        $order = Order::create(
            $orderId,
            $dto->customerId,
            $dto->customerEmail,
            $items,
            $dto->currency
        );

        // Apply bulk discount if applicable
        $discountedTotal = $this->orderDomainService->applyBulkDiscount(
            $order->getTotalAmount(),
            $order->getTotalQuantity()
        );

        // Save order
        $this->orderRepository->save($order);

        // Update product inventory
        foreach ($items as $item) {
            $product = $this->productRepository->findById($item->getProductId());
            if ($product) {
                $product->decreaseQuantity($item->getQuantity(), 'order_fulfillment', $orderId->toString());
                $this->productRepository->save($product);
            }
        }

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function getOrderById(string $orderId): ?OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            return null;
        }

        return $this->mapToDTO($order);
    }

    public function getOrdersByCustomerId(string $customerId): array
    {
        $orders = $this->orderRepository->findByCustomerId($customerId);
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function getOrdersByStatus(string $status): array
    {
        $orders = $this->orderRepository->findByStatus(OrderStatus::fromString($status));
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function updateOrder(string $orderId, UpdateOrderDTO $dto): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        // Update fields if provided
        if ($dto->shippingAddress !== null) {
            $order->updateShippingAddress($dto->shippingAddress);
        }

        if ($dto->billingAddress !== null) {
            $order->updateBillingAddress($dto->billingAddress);
        }

        if ($dto->notes !== null) {
            $order->updateNotes($dto->notes);
        }

        // Save changes
        $this->orderRepository->save($order);

        return $this->mapToDTO($order);
    }

    public function confirmOrder(string $orderId, ?string $confirmedBy = null): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $order->confirm($confirmedBy);
        $this->orderRepository->save($order);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function startProcessing(string $orderId, ?string $processedBy = null): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $order->startProcessing($processedBy);
        $this->orderRepository->save($order);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function shipOrder(string $orderId, string $trackingNumber, ?string $shippedBy = null): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $order->ship($trackingNumber, $shippedBy);
        $this->orderRepository->save($order);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function deliverOrder(string $orderId, ?string $deliveredBy = null): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $order->deliver($deliveredBy);
        $this->orderRepository->save($order);

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function cancelOrder(string $orderId, ?string $reason = null, ?string $cancelledBy = null): OrderDTO
    {
        $order = $this->orderRepository->findById(OrderId::fromString($orderId));
        
        if (!$order) {
            throw new \InvalidArgumentException('Order not found');
        }

        $order->cancel($reason, $cancelledBy);
        $this->orderRepository->save($order);

        // Restore inventory
        foreach ($order->getItems() as $item) {
            $product = $this->productRepository->findById($item->getProductId());
            if ($product) {
                $product->increaseQuantity($item->getQuantity(), 'order_cancellation', $orderId);
                $this->productRepository->save($product);
            }
        }

        // Dispatch domain events
        $this->eventDispatcher->dispatch($order->getDomainEvents());
        $order->clearDomainEvents();

        return $this->mapToDTO($order);
    }

    public function getActiveOrders(): array
    {
        $orders = $this->orderRepository->findActiveOrders();
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function getPendingOrders(): array
    {
        $orders = $this->orderRepository->findPendingOrders();
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function getBulkOrders(): array
    {
        $orders = $this->orderRepository->findBulkOrders();
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function getOrdersNeedingApproval(): array
    {
        $orders = $this->orderRepository->findOrdersNeedingApproval();
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function searchOrders(string $keyword): array
    {
        $orders = $this->orderRepository->searchByKeyword($keyword);
        
        return array_map([$this, 'mapToDTO'], $orders);
    }

    public function calculateShippingCost(array $items, string $region = 'domestic'): float
    {
        $totalAmount = array_sum(array_map(fn($item) => $item['unit_price'] * $item['quantity'], $items));
        $totalQuantity = array_sum(array_map(fn($item) => $item['quantity'], $items));

        return $this->orderDomainService->calculateShippingCost($totalAmount, $totalQuantity, $region);
    }

    public function estimateDeliveryTime(array $items, string $region = 'domestic'): \DateInterval
    {
        $totalQuantity = array_sum(array_map(fn($item) => $item['quantity'], $items));
        return $this->orderDomainService->estimateDeliveryTime($region, $totalQuantity);
    }

    public function generateOrderReport(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $orders = $this->orderRepository->findOrdersInDateRange($startDate, $endDate);
        return $this->orderDomainService->generateOrderReport($orders);
    }

    private function mapToDTO(Order $order): OrderDTO
    {
        return new OrderDTO(
            $order->getId()->toString(),
            $order->getCustomerId(),
            $order->getCustomerEmail(),
            array_map(fn($item) => $item->toArray(), $order->getItems()),
            $order->getTotalAmount(),
            $order->getCurrency(),
            $order->getStatus()->getValue(),
            $order->getShippingAddress(),
            $order->getBillingAddress(),
            $order->getNotes(),
            $order->getCreatedAt(),
            $order->getUpdatedAt(),
            $order->getTrackingNumber(),
            $order->getStatus()->getDisplayName(),
            $order->getStatus()->getColorClass(),
            $order->isBulkOrder(),
            $order->isHighValueOrder()
        );
    }
}