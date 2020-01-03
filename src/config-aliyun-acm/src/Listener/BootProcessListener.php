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

namespace Hyperf\ConfigAliyunAcm\Listener;

use Hyperf\ConfigAliyunAcm\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Utils\Packer\JsonPacker;
use Psr\Container\ContainerInterface;

class BootProcessListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var PackerInterface
     */
    private $packer;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->client = $container->get(ClientInterface::class);
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeProcessHandle::class,
        ];
    }

    public function process(object $event)
    {
        if (! $this->config->get('aliyun_acm.enable', false)) {
            return;
        }

        if ($config = $this->client->pull()) {
            foreach ($config as $key => $value) {
                if (is_string($key)) {
                    $this->config->set($key, $value);
                    $this->logger->debug(sprintf('Config [%s] is updated', $key));
                }
            }
        }
    }
}
