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
use Hyperf\SuperGlobals\Exception\ContainerNotFoundException;
use Hyperf\SuperGlobals\Exception\RequestNotFoundException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use JsonSerializable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Proxy implements Arrayable, ArrayAccess, JsonSerializable
{
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function offsetExists($offset)
    {
        $data = $this->toArray();
        return isset($data[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->toArray()[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($offset, $value) {
            $data = $this->toArray();
            $data[$offset] = $value;
            return $this->override($request, $data);
        });
    }

    public function offsetUnset($offset)
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

    abstract protected function override(ServerRequestInterface $request, array $data): ServerRequestInterface;
}
