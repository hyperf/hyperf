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

namespace Hyperf\Kafka;

use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ProducerManager
{
    /**
     * @var array<string, Producer>
     */
    private array $producers = [];

    public function __construct(private ContainerInterface $container)
    {
    }

    public function getProducer(string $name = 'default'): Producer
    {
        if (isset($this->producers[$name])) {
            return $this->producers[$name];
        }
        $this->producers[$name] = make(Producer::class, ['name' => $name]);
        return $this->producers[$name];
    }

    public function closeAll(): void
    {
        foreach ($this->producers as $producer) {
            $producer->close();
        }
    }
}
