<?php

namespace App\Events\Contracts;

interface ListenerInterface
{
    /**
     * 处理事件
     */
    public function handle(EventInterface $event): void;

    /**
     * 获取监听器名称
     */
    public function getName(): string;

    /**
     * 获取监听器优先级
     */
    public function getPriority(): int;

    /**
     * 检查是否应该处理此事件
     */
    public function shouldHandle(EventInterface $event): bool;

    /**
     * 获取监听器支持的事件类型
     */
    public function getSupportedEvents(): array;

    /**
     * 停止事件传播
     */
    public function stopPropagation(): bool;

    /**
     * 设置停止传播标志
     */
    public function setStopPropagation(bool $stop): self;
}