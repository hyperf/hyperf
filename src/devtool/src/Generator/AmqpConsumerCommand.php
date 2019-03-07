<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Devtool\Generator;

use Hyperf\Framework\Annotation\Command;

/**
 * @Command
 */
class AmqpConsumerCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:amqp-consumer');
        $this->setDescription('Create a new amqp consumer class');
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/amqp-consumer.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return 'App\\Amqp\\Consumers';
    }
}
