<?php

namespace App\Events\Listeners;

use App\Events\AbstractListener;
use App\Events\Contracts\EventInterface;
use App\Events\User\UserRegisteredEvent;
use App\Events\User\UserLoggedInEvent;
use App\Events\User\UserUpdatedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderStatusChangedEvent;
use App\Events\Order\OrderCancelledEvent;
use App\Events\Product\ProductCreatedEvent;
use App\Events\Product\ProductUpdatedEvent;
use App\Events\Product\ProductViewedEvent;
use App\Events\Inquiry\InquiryCreatedEvent;
use App\Events\Inquiry\InquiryStatusChangedEvent;
use Illuminate\Support\Facades\Log;

class LoggingListener extends AbstractListener
{
    protected int $priority = 1; // 最低优先级，最后执行
    protected array $supportedEvents = []; // 空数组表示监听所有事件

    public function handle(EventInterface $event): void
    {
        $this->safeHandle($event, function ($event) {
            $this->logEventByType($event);
        });
    }

    protected function logEventByType(EventInterface $event): void
    {
        $logData = [
            'event_id' => $event->getId(),
            'event_name' => $event->getName(),
            'timestamp' => $event->getTimestamp()->format('Y-m-d H:i:s'),
            'async' => $event->shouldProcessAsync(),
            'priority' => $event->getPriority(),
            'metadata' => $event->getMetadata()
        ];

        switch (true) {
            case $event instanceof UserRegisteredEvent:
                $this->logUserRegistered($event, $logData);
                break;
            case $event instanceof UserLoggedInEvent:
                $this->logUserLoggedIn($event, $logData);
                break;
            case $event instanceof UserUpdatedEvent:
                $this->logUserUpdated($event, $logData);
                break;
            case $event instanceof OrderCreatedEvent:
                $this->logOrderCreated($event, $logData);
                break;
            case $event instanceof OrderStatusChangedEvent:
                $this->logOrderStatusChanged($event, $logData);
                break;
            case $event instanceof OrderCancelledEvent:
                $this->logOrderCancelled($event, $logData);
                break;
            case $event instanceof ProductCreatedEvent:
                $this->logProductCreated($event, $logData);
                break;
            case $event instanceof ProductUpdatedEvent:
                $this->logProductUpdated($event, $logData);
                break;
            case $event instanceof ProductViewedEvent:
                $this->logProductViewed($event, $logData);
                break;
            case $event instanceof InquiryCreatedEvent:
                $this->logInquiryCreated($event, $logData);
                break;
            case $event instanceof InquiryStatusChangedEvent:
                $this->logInquiryStatusChanged($event, $logData);
                break;
            default:
                $this->logGenericEvent($event, $logData);
        }
    }

    protected function logUserRegistered(UserRegisteredEvent $event, array $logData): void
    {
        Log::info('User registered', array_merge($logData, [
            'user_id' => $event->getUserId(),
            'username' => $event->getUsername(),
            'email' => $event->getEmail(),
            'registration_ip' => $event->getRegistrationIp(),
            'user_agent' => $event->getUserAgent()
        ]));
    }

    protected function logUserLoggedIn(UserLoggedInEvent $event, array $logData): void
    {
        Log::info('User logged in', array_merge($logData, [
            'user_id' => $event->getUserId(),
            'username' => $event->getUsername(),
            'login_ip' => $event->getLoginIp(),
            'user_agent' => $event->getUserAgent(),
            'login_time' => $event->getLoginTime()
        ]));
    }

    protected function logUserUpdated(UserUpdatedEvent $event, array $logData): void
    {
        Log::info('User updated', array_merge($logData, [
            'user_id' => $event->getUserId(),
            'username' => $event->getUsername(),
            'changed_fields' => $event->getChangedFields(),
            'updated_by' => $event->getUpdatedBy(),
            'update_time' => $event->getUpdateTime()
        ]));
    }

    protected function logOrderCreated(OrderCreatedEvent $event, array $logData): void
    {
        Log::info('Order created', array_merge($logData, [
            'order_id' => $event->getOrderId(),
            'order_number' => $event->getOrderNumber(),
            'user_id' => $event->getUserId(),
            'total_amount' => $event->getTotalAmount(),
            'currency' => $event->getCurrency(),
            'items_count' => $event->getItemsCount(),
            'status' => $event->getStatus()
        ]));
    }

    protected function logOrderStatusChanged(OrderStatusChangedEvent $event, array $logData): void
    {
        Log::info('Order status changed', array_merge($logData, [
            'order_id' => $event->getOrderId(),
            'order_number' => $event->getOrderNumber(),
            'user_id' => $event->getUserId(),
            'old_status' => $event->getOldStatus(),
            'new_status' => $event->getNewStatus(),
            'changed_by' => $event->getChangedBy(),
            'reason' => $event->getReason(),
            'is_upgrade' => $event->isStatusUpgrade()
        ]));
    }

    protected function logOrderCancelled(OrderCancelledEvent $event, array $logData): void
    {
        Log::warning('Order cancelled', array_merge($logData, [
            'order_id' => $event->getOrderId(),
            'order_number' => $event->getOrderNumber(),
            'user_id' => $event->getUserId(),
            'total_amount' => $event->getTotalAmount(),
            'cancellation_reason' => $event->getCancellationReason(),
            'cancelled_by' => $event->getCancelledBy(),
            'requires_refund' => $event->requiresRefund()
        ]));
    }

    protected function logProductCreated(ProductCreatedEvent $event, array $logData): void
    {
        Log::info('Product created', array_merge($logData, [
            'product_id' => $event->getProductId(),
            'sku' => $event->getSku(),
            'name' => $event->getName(),
            'price' => $event->getPrice(),
            'currency' => $event->getCurrency(),
            'stock_quantity' => $event->getStockQuantity(),
            'category' => $event->getCategory(),
            'created_by' => $event->getCreatedBy()
        ]));
    }

    protected function logProductUpdated(ProductUpdatedEvent $event, array $logData): void
    {
        Log::info('Product updated', array_merge($logData, [
            'product_id' => $event->getProductId(),
            'sku' => $event->getSku(),
            'name' => $event->getName(),
            'changed_fields' => $event->getChangedFields(),
            'stock_change' => $event->getStockChange(),
            'stock_changed' => $event->hasStockChanged(),
            'price_changed' => $event->hasPriceChanged(),
            'updated_by' => $event->getUpdatedBy()
        ]));
    }

    protected function logProductViewed(ProductViewedEvent $event, array $logData): void
    {
        Log::debug('Product viewed', array_merge($logData, [
            'product_id' => $event->getProductId(),
            'sku' => $event->getSku(),
            'name' => $event->getName(),
            'viewer_id' => $event->getViewerId(),
            'viewer_ip' => $event->getViewerIp(),
            'is_authenticated' => $event->isViewedByAuthenticatedUser(),
            'session_id' => $event->getSessionId(),
            'referrer' => $event->getReferrer()
        ]));
    }

    protected function logInquiryCreated(InquiryCreatedEvent $event, array $logData): void
    {
        Log::info('Inquiry created', array_merge($logData, [
            'inquiry_id' => $event->getInquiryId(),
            'inquiry_number' => $event->getInquiryNumber(),
            'user_id' => $event->getUserId(),
            'company_name' => $event->getCompanyName(),
            'contact_person' => $event->getContactPerson(),
            'email' => $event->getEmail(),
            'priority' => $event->getPriority(),
            'estimated_budget' => $event->getEstimatedBudget(),
            'has_budget' => $event->hasBudget(),
            'quantity' => $event->getQuantity(),
            'ip_address' => $event->getIpAddress()
        ]));
    }

    protected function logInquiryStatusChanged(InquiryStatusChangedEvent $event, array $logData): void
    {
        Log::info('Inquiry status changed', array_merge($logData, [
            'inquiry_id' => $event->getInquiryId(),
            'inquiry_number' => $event->getInquiryNumber(),
            'user_id' => $event->getUserId(),
            'company_name' => $event->getCompanyName(),
            'old_status' => $event->getOldStatus(),
            'new_status' => $event->getNewStatus(),
            'changed_by' => $event->getChangedBy(),
            'reason' => $event->getReason(),
            'is_progress' => $event->isStatusProgress(),
            'priority' => $event->getPriority()
        ]));
    }

    protected function logGenericEvent(EventInterface $event, array $logData): void
    {
        Log::info('Generic event processed', array_merge($logData, [
            'event_data' => $event->getData()
        ]));
    }
}