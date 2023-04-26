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
namespace Hyperf\Pool\SimplePool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool as AbstractPool;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class Pool extends AbstractPool
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(ContainerInterface $container, callable $callback, array $option)
    {
        $this->callback = $callback;

        parent::__construct($container, $option);
    }

    protected function createConnection(): ConnectionInterface
    {
        return make(Connection::class, [
            'pool' => $this,
            'callback' => $this->callback,
        ]);
    }
}
