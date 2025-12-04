<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Contracts\DomainEventInterface;

final class EventDispatcherService
{
    private array $handlers = [];

    public function register(string $eventType, callable $handler): void
    {
        $this->handlers[$eventType][] = $handler;
    }

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            if (!$event instanceof DomainEventInterface) {
                continue;
            }

            $eventName = $event->getEventName();
            
            if (isset($this->handlers[$eventName])) {
                foreach ($this->handlers[$eventName] as $handler) {
                    try {
                        $handler($event);
                    } catch (\Exception $e) {
                        // Log error but continue processing other events
                        error_log("Error handling event {$eventName}: " . $e->getMessage());
                    }
                }
            }
        }
    }

    public function dispatchSingle(DomainEventInterface $event): void
    {
        $this->dispatch([$event]);
    }

    public function hasHandlers(string $eventType): bool
    {
        return isset($this->handlers[$eventType]) && !empty($this->handlers[$eventType]);
    }

    public function getHandlerCount(string $eventType): int
    {
        return count($this->handlers[$eventType] ?? []);
    }
}