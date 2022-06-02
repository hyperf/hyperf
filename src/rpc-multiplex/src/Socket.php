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
namespace Hyperf\RpcMultiplex;

use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Multiplex\Constract\IdGeneratorInterface;
use Multiplex\Constract\PackerInterface;
use Multiplex\Constract\SerializerInterface;
use Multiplex\Exception\ChannelClosedException;
use Multiplex\Packet;
use Psr\Container\ContainerInterface;

class Socket extends \Multiplex\Socket\Client
{
    /**
     * client keep healthy config
     * [callable $callback, $index]
     */
    protected $keepHealthyConf = [];

    public $isHealthy = true;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            '',
            80,
            $container->get(IdGeneratorInterface::class),
            $container->get(SerializerInterface::class),
            $container->get(PackerInterface::class)
        );
    }

    /**
     * @return $this
     */
    public function setName(string $name): Socket
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return array
     */
    public function getInfo() {
        return [
            'host' => $this->name,
            'port' => $this->port,
            'isHealthy' => $this->isHealthy,
        ];
    }

    public function keepHealthy(callable $callback, $index) {
        $this->keepHealthyConf = [$callback, $index];
    }

    protected function invokeKeepHealthy() {
        if (empty($this->keepHealthyConf)) {
            return;
        }
        try {
            call_user_func(...$this->keepHealthyConf);
            $this->loop();
            $this->isHealthy = true;
        } catch (\Throwable $th) {
            $this->logger && $this->logger->error('invokeKeepHealthy error ' . $th->getMessage());
        }
    }

    protected function heartbeat(): void
    {
        $heartbeat = $this->config->get('heartbeat');
        if (! $this->heartbeat && is_numeric($heartbeat)) {
            $this->heartbeat = true;

            Coroutine::create(function () use ($heartbeat) {
                while (true) {
                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($heartbeat)) {
                        break;
                    }

                    try {
                        // PING
                        if ($chan = $this->chan and $chan->isEmpty()) {
                            if ($chan->isClosing()) {
                                $this->isHealthy = false;
                                throw new ChannelClosedException(sprintf('chan is closed %s:%d', $this->name, $this->port));
                            }
                            $payload = $this->packer->pack(
                                new Packet(0, Packet::PING)
                            );
                            $chan->push($payload);
                        }
                    } catch (\Throwable $exception) {
                        $this->logger && $this->logger->error('multiplex socket heartbeat error ', [
                            'error' => $exception,
                        ]);
                        $this->invokeKeepHealthy();
                    }
                }
            });
        }
    }
}
