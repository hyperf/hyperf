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
namespace Hyperf\SuperGlobals;

use ArrayAccess;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\Arrayable;
use Hyperf\SuperGlobals\Exception\ContainerNotFoundException;
use Hyperf\SuperGlobals\Exception\RequestNotFoundException;
use JsonSerializable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Proxy implements Arrayable, ArrayAccess, JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        $data = $this->toArray();
        return isset($data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($offset, $value) {
            $data = $this->toArray();
            $data[$offset] = $value;
            return $this->override($request, $data);
        });
    }

    public function offsetUnset(mixed $offset): void
    {
        Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($offset) {
            $data = $this->toArray();
            unset($data[$offset]);
            return $this->override($request, $data);
        });
    }

    protected function getContainer(): ContainerInterface
    {
        if (! ApplicationContext::hasContainer()) {
            throw new ContainerNotFoundException();
        }

        return ApplicationContext::getContainer();
    }

    protected function getRequest(): ServerRequestInterface
    {
        $request = Context::get(ServerRequestInterface::class);
        if (! $request instanceof ServerRequestInterface) {
            throw new RequestNotFoundException(sprintf('%s is not found.', ServerRequestInterface::class));
        }

        return $request;
    }

    protected function hasRequest(): bool
    {
        return Context::has(ServerRequestInterface::class);
    }

    abstract protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface;
}
