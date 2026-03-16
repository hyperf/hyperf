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

namespace Hyperf\ConfigCenter\Process;

use Hyperf\ConfigCenter\DriverFactory;
use Hyperf\ConfigCenter\Mode;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server;

class ConfigFetcherProcess extends AbstractProcess
{
    public string $name = 'config-center-fetcher';

    /**
     * @var Server
     */
    protected $server;

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    protected DriverFactory $driverFactory;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->driverFactory = $container->get(DriverFactory::class);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable($server): bool
    {
        return $server instanceof Server
            && $this->config->get('config_center.enable', false)
            && strtolower($this->config->get('config_center.mode', Mode::PROCESS)) === Mode::PROCESS;
    }

    public function handle(): void
    {
        $driver = $this->config->get('config_center.driver', '');
        if (! $driver) {
            return;
        }
        $instance = $this->driverFactory->create($driver, [
            'setServer' => $this->server,
        ]);
        $instance->createMessageFetcherLoop();
        while (ProcessManager::isRunning()) {
            sleep(1);
        }
    }
}
