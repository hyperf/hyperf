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

namespace Hyperf\Framework;

use Throwable;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Dispatcher\AbstractDispatcher;

class ExceptionHandlerDispatcher extends AbstractDispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(...$params): ResponseInterface
    {
        return parent::dispatch(...$params);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(...$params)
    {
        /**
         * @var Throwable
         * @var string[] $handlers
         */
        [$throwable, $handlers] = $params;
        $response = Context::get(ResponseInterface::class);
        foreach ($handlers as $handler) {
            if (! $this->container->has($handler)) {
                throw new \InvalidArgumentException(sprintf('Invalid exception handler %s.', $handler));
            }
            $handlerInstance = $this->container->get($handler);
            if (! $handlerInstance instanceof ExceptionHandler || ! $handlerInstance->isValid($throwable)) {
                continue;
            }
            $response = $handlerInstance->handle($throwable, $response);
            if ($handlerInstance->isPropagationStopped()) {
                break;
            }
        }
        return $response;
    }
}
