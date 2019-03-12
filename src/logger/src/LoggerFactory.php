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

namespace Hyperf\Logger;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\Exception\InvalidConfigException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $loggers;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function make($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        $config = $this->config->get('logger');
        if (! isset($config[$group])) {
            throw new InvalidConfigException(sprintf('Logger config[%s] is not defined.', $name));
        }

        $config = $config[$group];
        $handler = $this->handler($config);

        return make(Logger::class, [
            'name' => $name,
            'handlers' => [$handler],
        ]);
    }

    public function get($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        if (isset($this->loggers[$name]) && $this->loggers[$name] instanceof Logger) {
            return $this->loggers[$name];
        }

        return $this->loggers[$name] = $this->make($name, $group);
    }

    protected function handler(array $config): HandlerInterface
    {
        $handlerClass = $config['handler']['class'];
        $handlerConstructor = $config['handler']['constructor'];

        /** @var HandlerInterface $handler */
        $handler = make($handlerClass, $handlerConstructor);

        $formatterClass = $config['formatter']['class'];
        $formatterConstructor = $config['formatter']['constructor'];

        /** @var FormatterInterface $formatter */
        $formatter = make($formatterClass, $formatterConstructor);

        $handler->setFormatter($formatter);

        return $handler;
    }
}
