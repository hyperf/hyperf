<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Hyperf\XxlJob\Application;
use Throwable;

class MainWorkerStartListener implements ListenerInterface
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(Application $app, StdoutLoggerInterface $logger)
    {
        $this->app = $app;
        $this->logger = $logger;
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event)
    {
        $config = $this->app->getConfig();
        if (! $config->isEnable()) {
            return;
        }
        $this->registerHeartbeat($config->getAppName(), $config->getClientUrl(), $config->getHeartbeat());
    }

    protected function registerHeartbeat(string $appName, string $url, $heartbeat = 30): void
    {
        Coroutine::create(function () use ($appName, $url, $heartbeat) {
            retry(INF, function () use ($appName, $url, $heartbeat) {
                while (true) {
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                        break;
                    }
                    try {
                        $this->app->service->registry($appName, $url);
                        $this->logger->debug(sprintf('xxlJob registry app name:%s heartbeat successfully', $appName));
                    } catch (Throwable $throwable) {
                        $this->logger->error($throwable);
                        throw $throwable;
                    }
                }
            });
        });
    }
}
