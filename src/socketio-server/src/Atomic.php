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
namespace Hyperf\SocketIOServer;

use Hyperf\Server\Server;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Swoole\Atomic as SwooleAtomic;

class Atomic
{
    protected ?SwooleAtomic $atomic = null;

    protected int $id = 0;

    public function __construct(?ContainerInterface $container = null)
    {
        if ($config = $container?->get(ServerFactory::class)->getConfig() and $config->getType() === Server::class) {
            $this->atomic = new SwooleAtomic();
        }
    }

    public function get(): int
    {
        if ($this->atomic) {
            return $this->atomic->get();
        }

        return $this->id;
    }

    public function add(): int
    {
        if ($this->atomic) {
            return $this->atomic->add();
        }

        return ++$this->id;
    }
}
