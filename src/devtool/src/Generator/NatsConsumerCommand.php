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
namespace Hyperf\Devtool\Generator;

use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class NatsConsumerCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:nats-consumer');
        $this->setDescription('Create a new nats consumer class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/nats-consumer.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Nats\\Consumer';
    }
}
