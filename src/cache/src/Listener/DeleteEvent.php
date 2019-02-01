<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Cache\Listener;

class DeleteEvent
{
    protected $className;

    protected $method;

    protected $arguments;

    public function __construct(string $className, string $method, array $arguments)
    {
        $this->className = $className;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
