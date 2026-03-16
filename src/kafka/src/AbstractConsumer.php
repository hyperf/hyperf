<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Kafka;

use longlang\phpkafka\Consumer\ConsumeMessage;

abstract class AbstractConsumer
{
    public string $name = 'kafka';

    public string $pool = 'default';

    /**
     * @var string|string[]
     */
    public array|string $topic = [];

    public ?string $groupId = null;

    public ?string $memberId = null;

    public ?string $groupInstanceId = null;

    public bool $autoCommit = true;

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): void
    {
        $this->pool = $pool;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic($topic): void
    {
        $this->topic = $topic;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getMemberId(): ?string
    {
        return $this->memberId;
    }

    public function setMemberId(?string $memberId): void
    {
        $this->memberId = $memberId;
    }

    public function getGroupInstanceId(): ?string
    {
        return $this->groupInstanceId;
    }

    public function setGroupInstanceId(?string $groupInstanceId): void
    {
        $this->groupInstanceId = $groupInstanceId;
    }

    public function isAutoCommit(): bool
    {
        return $this->autoCommit;
    }

    public function setAutoCommit(bool $autoCommit): void
    {
        $this->autoCommit = $autoCommit;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isEnable(bool $enable): bool
    {
        return $enable;
    }

    /**
     * @return null|string
     */
    abstract public function consume(ConsumeMessage $message);
}
