<?php

namespace App\Events\Contracts;

interface EventInterface
{
    /**
     * 获取事件名称
     */
    public function getName(): string;

    /**
     * 获取事件数据
     */
    public function getData(): array;

    /**
     * 获取事件发生时间
     */
    public function getTimestamp(): \DateTime;

    /**
     * 获取事件唯一标识
     */
    public function getId(): string;

    /**
     * 获取事件元数据
     */
    public function getMetadata(): array;

    /**
     * 设置事件元数据
     */
    public function setMetadata(array $metadata): self;

    /**
     * 检查事件是否应该异步处理
     */
    public function shouldProcessAsync(): bool;

    /**
     * 获取事件优先级
     */
    public function getPriority(): int;

    /**
     * 序列化事件数据
     */
    public function serialize(): string;

    /**
     * 反序列化事件数据
     */
    public static function deserialize(string $data): self;
}