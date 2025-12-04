<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Infrastructure\Repositories\EloquentProductRepository;
use App\Infrastructure\Repositories\EloquentOrderRepository;
use App\Domain\User\Services\UserDomainService;
use App\Domain\Product\Services\ProductDomainService;
use App\Domain\Order\Services\OrderDomainService;
use App\Application\Services\UserApplicationService;
use App\Application\Services\OrderApplicationService;
use App\Application\Services\EventDispatcherService;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);

        // Domain service bindings
        $this->app->singleton(UserDomainService::class, function ($app) {
            return new UserDomainService($app->make(UserRepositoryInterface::class));
        });

        $this->app->singleton(ProductDomainService::class, function ($app) {
            return new ProductDomainService($app->make(ProductRepositoryInterface::class));
        });

        $this->app->singleton(OrderDomainService::class, function ($app) {
            return new OrderDomainService(
                $app->make(OrderRepositoryInterface::class),
                $app->make(ProductRepositoryInterface::class)
            );
        });

        // Application service bindings
        $this->app->singleton(UserApplicationService::class, function ($app) {
            return new UserApplicationService(
                $app->make(UserRepositoryInterface::class),
                $app->make(UserDomainService::class),
                $app->make(EventDispatcherService::class)
            );
        });

        $this->app->singleton(OrderApplicationService::class, function ($app) {
            return new OrderApplicationService(
                $app->make(OrderRepositoryInterface::class),
                $app->make(ProductRepositoryInterface::class),
                $app->make(OrderDomainService::class),
                $app->make(EventDispatcherService::class)
            );
        });

        // Event dispatcher
        $this->app->singleton(EventDispatcherService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register event handlers here
        $eventDispatcher = $this->app->make(EventDispatcherService::class);
        
        // Example: Register user event handlers
        $eventDispatcher->register('user_registered', function ($event) {
            // Send welcome email, create initial settings, etc.
            \Log::info('User registered', [
                'user_id' => $event->getAggregateId(),
                'email' => $event->getEmail(),
                'username' => $event->getUsername()
            ]);
        });

        $eventDispatcher->register('user_status_changed', function ($event) {
            // Handle user status changes
            \Log::info('User status changed', [
                'user_id' => $event->getAggregateId(),
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'reason' => $event->getReason()
            ]);
        });

        // Example: Register order event handlers
        $eventDispatcher->register('order_created', function ($event) {
            // Send order confirmation, update inventory, etc.
            \Log::info('Order created', [
                'order_id' => $event->getAggregateId(),
                'customer_id' => $event->getCustomerId(),
                'total_amount' => $event->getTotalAmount(),
                'is_bulk_order' => $event->isBulkOrder()
            ]);
        });

        $eventDispatcher->register('order_status_changed', function ($event) {
            // Send status notifications, update analytics, etc.
            \Log::info('Order status changed', [
                'order_id' => $event->getAggregateId(),
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'requires_notification' => $event->requiresNotification()
            ]);
        });

        // Example: Register product event handlers
        $eventDispatcher->register('product_created', function ($event) {
            // Index for search, notify suppliers, etc.
            \Log::info('Product created', [
                'product_id' => $event->getAggregateId(),
                'name' => $event->getName(),
                'price' => $event->getPrice()
            ]);
        });

        $eventDispatcher->register('inventory_changed', function ($event) {
            // Check reorder levels, send alerts, etc.
            \Log::info('Inventory changed', [
                'product_id' => $event->getAggregateId(),
                'old_quantity' => $event->getOldQuantity(),
                'new_quantity' => $event->getNewQuantity(),
                'needs_restock' => $event->needsRestock()
            ]);
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            UserRepositoryInterface::class,
            ProductRepositoryInterface::class,
            OrderRepositoryInterface::class,
            UserDomainService::class,
            ProductDomainService::class,
            OrderDomainService::class,
            UserApplicationService::class,
            OrderApplicationService::class,
            EventDispatcherService::class,
        ];
    }
}