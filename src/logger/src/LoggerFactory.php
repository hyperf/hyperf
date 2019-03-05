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

use Psr\Log\LoggerInterface;
use Hyperf\Contract\ConfigInterface;
use Monolog\Handler\HandlerInterface;
use Psr\Container\ContainerInterface;
use Monolog\Formatter\FormatterInterface;
use Hyperf\Logger\Exceptions\InvalidConfigException;

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

    public function make($name = 'default', $subname = null): LoggerInterface
    {
        $config = $this->config->get('logger');
        if (! isset($config[$name])) {
            throw new InvalidConfigException(sprintf('Logger config[%s] is not defined.', $name));
        }

        $config = $config[$name];
        $handler = $this->handler($config);

        $subname && $name = $name . '.' . $subname;

        return make(Logger::class, [
            'name' => $name,
            'handlers' => [$handler],
        ]);
    }

    public function get($name = 'default'): LoggerInterface
    {
        if (isset($this->loggers[$name]) && $this->loggers[$name] instanceof Logger) {
            return $this->loggers[$name];
        }

        return $this->loggers[$name] = $this->make($name);
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
