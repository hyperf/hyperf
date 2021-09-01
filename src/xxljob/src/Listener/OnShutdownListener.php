<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\XxlJob\Application;
use Psr\Container\ContainerInterface;

class OnShutdownListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var bool
     */
    private $processed = false;

    /**
     * @var Application
     */
    private $app;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->app = $container->get(Application::class);
    }

    public function listen(): array
    {
        return [
            OnShutdown::class,
            CoroutineServerStop::class,
        ];
    }

    public function process(object $event)
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        $config = $this->app->getConfig();
        if (! $config->isEnable()) {
            return;
        }
        $response = $this->app->service->registryRemove($config->getAppName(), $config->getClientUrl());
        if ($response->getStatusCode() === 200) {
            $this->logger->debug(sprintf('xxlJob app name:%s url:%s remove successfully!', $config->getAppName(), $config->getClientUrl()));
        } else {
            $this->logger->error(sprintf('xxlJob app name:%s url:%s remove failed!', $config->getAppName(), $config->getClientUrl()));
        }
    }
}
