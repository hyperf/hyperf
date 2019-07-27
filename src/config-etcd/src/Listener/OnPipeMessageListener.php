<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigEtcd\Listener;

use Hyperf\ConfigEtcd\ClientInterface;
use Hyperf\ConfigEtcd\KV;
use Hyperf\ConfigEtcd\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Utils\Packer\JsonPacker;
use Psr\Container\ContainerInterface;

/**
 * @Listener
 */
class OnPipeMessageListener implements ListenerInterface
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
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var PackerInterface
     */
    private $packer;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->client = $container->get(ClientInterface::class);

        $this->mapping = $this->config->get('etcd.mapping', []);
        $this->packer = $container->get($this->config->get('etcd.packer', JsonPacker::class));
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        if ($event instanceof OnPipeMessage && $event->data instanceof PipeMessage) {
            /** @var PipeMessage $data */
            $data = $event->data;

            /** @var KV $kv */
            foreach ($data->configurations ?? [] as $kv) {
                $key = $this->mapping[$kv->key] ?? null;
                if (is_string($key)) {
                    $this->config->set($key, $this->packer->unpack($kv->value));
                    $this->logger->debug(sprintf('Config [%s] is updated', $key));
                }
            }
        }
    }
}
