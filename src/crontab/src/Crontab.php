<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Crontab;

use Carbon\Carbon;

class Crontab
{
    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type = 'callback';

    /**
     * @var null|string
     */
    protected $rule;

    /**
     * @var mixed
     */
    protected $callback;

    /**
     * @var null|string
     */
    protected $memo;

    /**
     * @var null|\Carbon\Carbon
     */
    protected $executeTime;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Crontab
    {
        $this->name = $name;
        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(?string $rule): Crontab
    {
        $this->rule = $rule;
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setCallback($callback): Crontab
    {
        $this->callback = $callback;
        return $this;
    }

    public function getMemo(): ?string
    {
        return $this->memo;
    }

    public function setMemo(?string $memo): Crontab
    {
        $this->memo = $memo;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Crontab
    {
        $this->type = $type;
        return $this;
    }

    public function getExecuteTime(): ?Carbon
    {
        return $this->executeTime;
    }

    public function setExecuteTime(Carbon $executeTime): Crontab
    {
        $this->executeTime = $executeTime;
        return $this;
    }
}
