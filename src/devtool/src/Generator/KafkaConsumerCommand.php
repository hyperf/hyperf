<?php


namespace Hyperf\Devtool\Generator;

use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class KafkaConsumerCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('gen:kafka-consumer');
        $this->setDescription('Create a new kafka consumer class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/kafka-consumer.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Kafka\\Consumer';
    }
}
