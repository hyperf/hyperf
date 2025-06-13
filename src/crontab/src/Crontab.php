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

namespace Hyperf\Crontab;

use Carbon\Carbon;
use DateTimeZone;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Engine\Channel;

class Crontab
{
    use Conditionable;
    use ManagesFrequencies;

    protected ?string $name = null;

    protected string $type = 'callback';

    protected ?string $rule = null;

    protected bool $singleton = false;

    protected string $mutexPool = 'default';

    protected int $mutexExpires = 60;

    protected bool $onOneServer = false;

    protected mixed $callback = null;

    protected ?string $memo = null;

    protected ?Carbon $executeTime = null;

    protected bool $enable = true;

    protected null|DateTimeZone|string $timezone = null;

    protected ?Channel $running = null;

    protected array $environments = [];

    protected array $options = [];

    public function __clone()
    {
        $this->running = new Channel(1);
    }

    public function __serialize(): array
    {
        return [
            "\x00*\x00name" => $this->name,
            "\x00*\x00type" => $this->type,
            "\x00*\x00rule" => $this->rule,
            "\x00*\x00singleton" => $this->singleton,
            "\x00*\x00mutexPool" => $this->mutexPool,
            "\x00*\x00mutexExpires" => $this->mutexExpires,
            "\x00*\x00onOneServer" => $this->onOneServer,
            "\x00*\x00callback" => $this->callback,
            "\x00*\x00memo" => $this->memo,
            "\x00*\x00executeTime" => $this->executeTime,
            "\x00*\x00enable" => $this->enable,
            "\x00*\x00timezone" => $this->timezone,
            "\x00*\x00environments" => $this->environments,
            "\x00*\x00options" => $this->options,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data["\x00*\x00name"] ?? $this->name;
        $this->type = $data["\x00*\x00type"] ?? $this->type;
        $this->rule = $data["\x00*\x00rule"] ?? $this->rule;
        $this->singleton = $data["\x00*\x00singleton"] ?? $this->singleton;
        $this->mutexPool = $data["\x00*\x00mutexPool"] ?? $this->mutexPool;
        $this->mutexExpires = $data["\x00*\x00mutexExpires"] ?? $this->mutexExpires;
        $this->onOneServer = $data["\x00*\x00onOneServer"] ?? $this->onOneServer;
        $this->callback = $data["\x00*\x00callback"] ?? $this->callback;
        $this->memo = $data["\x00*\x00memo"] ?? $this->memo;
        $this->executeTime = $data["\x00*\x00executeTime"] ?? $this->executeTime;
        $this->enable = $data["\x00*\x00enable"] ?? $this->enable;
        $this->running = new Channel(1);
        $this->timezone = $data["\x00*\x00timezone"] ?? $this->timezone;
        $this->environments = $data["\x00*\x00environments"] ?? $this->environments;
        $this->options = $data["\x00*\x00options"] ?? $this->options;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(?string $rule): static
    {
        $this->rule = $rule;
        return $this;
    }

    public function isSingleton(): bool
    {
        return $this->singleton;
    }

    public function setSingleton(bool $singleton): static
    {
        $this->singleton = $singleton;
        return $this;
    }

    public function getMutexPool(): string
    {
        return $this->mutexPool;
    }

    public function setMutexPool(string $mutexPool): static
    {
        $this->mutexPool = $mutexPool;
        return $this;
    }

    public function getMutexExpires(): int
    {
        return $this->mutexExpires;
    }

    public function setMutexExpires(int $mutexExpires): static
    {
        $this->mutexExpires = $mutexExpires;
        return $this;
    }

    public function isOnOneServer(): bool
    {
        return $this->onOneServer;
    }

    public function setOnOneServer(bool $onOneServer): static
    {
        $this->onOneServer = $onOneServer;
        return $this;
    }

    public function getCallback(): mixed
    {
        return $this->callback;
    }

    public function setCallback(mixed $callback): static
    {
        $this->callback = $callback;
        return $this;
    }

    public function getMemo(): ?string
    {
        return $this->memo;
    }

    public function setMemo(?string $memo): static
    {
        $this->memo = $memo;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getExecuteTime(): ?Carbon
    {
        return $this->executeTime;
    }

    public function setExecuteTime(Carbon $executeTime): static
    {
        $this->executeTime = $executeTime;
        return $this;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;
        return $this;
    }

    public function getTimezone(): null|DateTimeZone|string
    {
        return $this->timezone;
    }

    public function setTimezone(DateTimeZone|string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Limit the environments the command should run in.
     *
     * @param array|mixed $environments
     * @return $this
     */
    public function setEnvironments($environments): static
    {
        $this->environments = is_array($environments) ? $environments : func_get_args();

        return $this;
    }

    public function getEnvironments(): array
    {
        return $this->environments;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function runsInEnvironment(string $environment): bool
    {
        return empty($this->environments) || in_array($environment, $this->environments, true);
    }

    public function complete(): void
    {
        $this->running?->close();
    }

    public function close(): void
    {
        $this->running?->close();
    }

    public function wait(): void
    {
        $this->running?->pop();
    }
}
