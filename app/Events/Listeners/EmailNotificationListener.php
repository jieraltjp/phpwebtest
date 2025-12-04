<?php

namespace App\Events\Listeners;

use App\Events\AbstractListener;
use App\Events\Contracts\EventInterface;
use App\Events\User\UserRegisteredEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderStatusChangedEvent;
use App\Events\Inquiry\InquiryCreatedEvent;
use App\Events\Inquiry\InquiryStatusChangedEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationListener extends AbstractListener
{
    protected int $priority = 10;
    protected array $supportedEvents = [
        UserRegisteredEvent::class,
        OrderCreatedEvent::class,
        OrderStatusChangedEvent::class,
        InquiryCreatedEvent::class,
        InquiryStatusChangedEvent::class,
    ];

    public function handle(EventInterface $event): void
    {
        $this->safeHandle($event, function ($event) {
            switch (true) {
                case $event instanceof UserRegisteredEvent:
                    $this->handleUserRegistered($event);
                    break;
                case $event instanceof OrderCreatedEvent:
                    $this->handleOrderCreated($event);
                    break;
                case $event instanceof OrderStatusChangedEvent:
                    $this->handleOrderStatusChanged($event);
                    break;
                case $event instanceof InquiryCreatedEvent:
                    $this->handleInquiryCreated($event);
                    break;
                case $event instanceof InquiryStatusChangedEvent:
                    $this->handleInquiryStatusChanged($event);
                    break;
                default:
                    $this->log($event, 'Email notification: No handler for event type');
            }
        });
    }

    protected function handleUserRegistered(UserRegisteredEvent $event): void
    {
        try {
            $data = [
                'username' => $event->getUsername(),
                'email' => $event->getEmail(),
                'name' => $event->getName(),
                'registration_time' => $event->getTimestamp()->format('Y-m-d H:i:s'),
                'registration_ip' => $event->getRegistrationIp(),
            ];

            // 发送欢迎邮件
            Mail::send('emails.welcome', $data, function ($message) use ($event) {
                $message->to($event->getEmail(), $event->getName())
                       ->subject('万方商事 - 欢迎注册');
            });

            // 发送管理员通知邮件
            Mail::send('emails.admin.user-registered', $data, function ($message) {
                $message->to('admin@manpou.jp', '万方商事管理员')
                       ->subject('新用户注册通知');
            });

            $this->log($event, 'Welcome email sent to user and admin notification sent');
        } catch (\Exception $e) {
            $this->logError($event, 'Failed to send user registration email', $e);
            throw $e;
        }
    }

    protected function handleOrderCreated(OrderCreatedEvent $event): void
    {
        try {
            $data = [
                'order_number' => $event->getOrderNumber(),
                'total_amount' => $event->getTotalAmount(),
                'currency' => $event->getCurrency(),
                'items' => $event->getItems(),
                'items_count' => $event->getItemsCount(),
                'created_at' => $event->getCreatedAt(),
            ];

            // 发送订单确认邮件给客户
            Mail::send('emails.order.confirmation', $data, function ($message) use ($event) {
                $user = \App\Models\User::find($event->getUserId());
                $message->to($user->email, $user->name ?? $user->username)
                       ->subject("万方商事 - 订单确认 #{$event->getOrderNumber()}");
            });

            // 发送新订单通知给管理员
            Mail::send('emails.admin.new-order', $data, function ($message) use ($event) {
                $message->to('orders@manpou.jp', '万方商事订单部')
                       ->subject("新订单 #{$event->getOrderNumber()}");
            });

            $this->log($event, 'Order confirmation email sent to customer and admin notification sent');
        } catch (\Exception $e) {
            $this->logError($event, 'Failed to send order creation email', $e);
            throw $e;
        }
    }

    protected function handleOrderStatusChanged(OrderStatusChangedEvent $event): void
    {
        if (!$event->requiresNotification()) {
            $this->log($event, 'Order status changed but notification not required');
            return;
        }

        try {
            $data = [
                'order_number' => $event->getOrderNumber(),
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'status_change_time' => $event->getStatusChangeTime(),
                'reason' => $event->getReason(),
                'total_amount' => $event->getTotalAmount(),
                'currency' => $event->getCurrency(),
            ];

            // 发送状态变更通知给客户
            Mail::send('emails.order.status-changed', $data, function ($message) use ($event) {
                $user = \App\Models\User::find($event->getUserId());
                $message->to($user->email, $user->name ?? $user->username)
                       ->subject("万方商事 - 订单状态更新 #{$event->getOrderNumber()}");
            });

            $this->log($event, 'Order status change notification email sent');
        } catch (\Exception $e) {
            $this->logError($event, 'Failed to send order status change email', $e);
            throw $e;
        }
    }

    protected function handleInquiryCreated(InquiryCreatedEvent $event): void
    {
        if (!$event->requiresNotification()) {
            $this->log($event, 'Inquiry created but notification not required');
            return;
        }

        try {
            $data = [
                'inquiry_number' => $event->getInquiryNumber(),
                'company_name' => $event->getCompanyName(),
                'contact_person' => $event->getContactPerson(),
                'email' => $event->getEmail(),
                'phone' => $event->getPhone(),
                'subject' => $event->getSubject(),
                'message' => $event->getMessage(),
                'priority' => $event->getPriority(),
                'estimated_budget' => $event->getEstimatedBudget(),
                'currency' => $event->getCurrency(),
                'quantity' => $event->getQuantity(),
                'created_at' => $event->getCreatedAt(),
            ];

            // 发送询价确认邮件给客户
            Mail::send('emails.inquiry.confirmation', $data, function ($message) use ($event) {
                $message->to($event->getEmail(), $event->getContactPerson())
                       ->subject("万方商事 - 询价确认 #{$event->getInquiryNumber()}");
            });

            // 发送新询价通知给销售团队
            Mail::send('emails.admin.new-inquiry', $data, function ($message) use ($event) {
                $priority = $event->isHighPriority() ? '[高优先级] ' : '';
                $message->to('sales@manpou.jp', '万方商事销售部')
                       ->subject("{$priority}新询价 #{$event->getInquiryNumber()}");
            });

            $this->log($event, 'Inquiry confirmation email sent to customer and sales team notified');
        } catch (\Exception $e) {
            $this->logError($event, 'Failed to send inquiry creation email', $e);
            throw $e;
        }
    }

    protected function handleInquiryStatusChanged(InquiryStatusChangedEvent $event): void
    {
        if (!$event->requiresNotification()) {
            $this->log($event, 'Inquiry status changed but notification not required');
            return;
        }

        try {
            $data = [
                'inquiry_number' => $event->getInquiryNumber(),
                'company_name' => $event->getCompanyName(),
                'contact_person' => $event->getContactPerson(),
                'email' => $event->getEmail(),
                'old_status' => $event->getOldStatus(),
                'new_status' => $event->getNewStatus(),
                'status_change_time' => $event->getStatusChangeTime(),
                'reason' => $event->getReason(),
                'notes' => $event->getNotes(),
            ];

            // 发送状态变更通知给客户
            Mail::send('emails.inquiry.status-changed', $data, function ($message) use ($event) {
                $message->to($event->getEmail(), $event->getContactPerson())
                       ->subject("万方商事 - 询价状态更新 #{$event->getInquiryNumber()}");
            });

            $this->log($event, 'Inquiry status change notification email sent');
        } catch (\Exception $e) {
            $this->logError($event, 'Failed to send inquiry status change email', $e);
            throw $e;
        }
    }
}