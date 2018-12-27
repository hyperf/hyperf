<?php

namespace Hyperf\Framework;


use Hyperf\Dispatcher\AbstractDispatcher;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

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
     * @inheritdoc
     */
    public function dispatch(...$params): ResponseInterface
    {
        return parent::dispatch(...$params);
    }

    /**
     * @inheritdoc
     */
    public function handle(...$params)
    {
        /**
         * @var Throwable $throwable
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