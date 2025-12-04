<?php

namespace App\Events;

use App\Events\Contracts\EventInterface;
use App\Events\Contracts\ListenerInterface;

abstract class AbstractListener implements ListenerInterface
{
    protected bool $stopPropagation = false;
    protected int $priority = 0;
    protected array $supportedEvents = [];

    public function getName(): string
    {
        return static::class;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function shouldHandle(EventInterface $event): bool
    {
        return empty($this->supportedEvents) || in_array($event->getName(), $this->supportedEvents);
    }

    public function getSupportedEvents(): array
    {
        return $this->supportedEvents;
    }

    public function stopPropagation(): bool
    {
        return $this->stopPropagation;
    }

    public function setStopPropagation(bool $stop): self
    {
        $this->stopPropagation = $stop;
        return $this;
    }

    /**
     * 记录监听器处理日志
     */
    protected function log(EventInterface $event, string $message, array $context = []): void
    {
        $context = array_merge($context, [
            'event_id' => $event->getId(),
            'event_name' => $event->getName(),
            'listener' => $this->getName(),
            'timestamp' => now()->toISOString()
        ]);

        \Log::info("[Event Listener] {$message}", $context);
    }

    /**
     * 记录监听器错误日志
     */
    protected function logError(EventInterface $event, string $message, \Throwable $exception = null): void
    {
        $context = [
            'event_id' => $event->getId(),
            'event_name' => $event->getName(),
            'listener' => $this->getName(),
            'timestamp' => now()->toISOString()
        ];

        if ($exception) {
            $context['exception'] = $exception->getMessage();
            $context['trace'] = $exception->getTraceAsString();
        }

        \Log::error("[Event Listener Error] {$message}", $context);
    }

    /**
     * 安全执行事件处理
     */
    protected function safeHandle(EventInterface $event, callable $handler): void
    {
        try {
            $this->log($event, 'Starting event processing');
            $handler($event);
            $this->log($event, 'Event processing completed');
        } catch (\Throwable $exception) {
            $this->logError($event, 'Event processing failed', $exception);
            throw $exception;
        }
    }
}