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

namespace Hyperf\ExceptionHandler\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler;
use Hyperf\ExceptionHandler\Exception\ServerNotFoundException;
use Hyperf\Framework\Event\BootApplication;

class ExceptionHandlerAnnotation implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * {@inheritdoc}
     * @throws ServerNotFoundException
     */
    public function process(object $event)
    {
        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);
        $configHandlerKey = 'exceptions.handler';

        $servers = $this->getServers($config);

        $config->set($configHandlerKey, $this->mergeAnnotationExceptionHandlers($config, $configHandlerKey, $servers));

        var_dump($config->get($configHandlerKey));
    }

    private function getServers(ConfigInterface $config)
    {
        $servers = $config->get('server.servers');
        $serverNames = [];
        foreach ($servers as $server) {
            $serverNames[$server['name']] = true;
        }

        return $serverNames;
    }

    /**
     * @throws ServerNotFoundException
     * @return mixed
     */
    private function mergeAnnotationExceptionHandlers(
        ConfigInterface $config,
        string $configHandlerKey,
        array $servers
    ): array {
        $collectorList = AnnotationCollector::list();
        $configHandlers = $config->get($configHandlerKey);
        foreach ($collectorList as $className => $metadata) {
            if (isset($metadata['_c'][ExceptionHandler::class])) {
                if (isset($servers[$metadata['_c'][ExceptionHandler::class]->server])) {
                    $configHandlers[$metadata['_c'][ExceptionHandler::class]->server][] = $className;
                } else {
                    throw new ServerNotFoundException('server <' . $metadata['_c'][ExceptionHandler::class]->server . '> not found');
                }
            }
        }
        return $configHandlers;
    }
}
