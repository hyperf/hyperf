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
namespace Hyperf\Logger;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\Exception\InvalidConfigException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function Hyperf\Support\make;

class LoggerFactory
{
    /**
     * @var LoggerInterface[]
     */
    protected array $loggers = [];

    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    public function make($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        $config = $this->config->get('logger');
        if (! isset($config[$group])) {
            throw new InvalidConfigException(sprintf('Logger config[%s] is not defined.', $group));
        }

        $config = $config[$group];
        $handlers = $this->handlers($config);
        $processors = $this->processors($config);

        return make(Logger::class, [
            'name' => $name,
            'handlers' => $handlers,
            'processors' => $processors,
        ]);
    }

    public function get($name = 'hyperf', $group = 'default'): LoggerInterface
    {
        if (isset($this->loggers[$group][$name]) && $this->loggers[$group][$name] instanceof Logger) {
            return $this->loggers[$group][$name];
        }

        return $this->loggers[$group][$name] = $this->make($name, $group);
    }

    protected function getDefaultFormatterConfig($config)
    {
        $formatterClass = Arr::get($config, 'formatter.class', LineFormatter::class);
        $formatterConstructor = Arr::get($config, 'formatter.constructor', []);

        return [
            'class' => $formatterClass,
            'constructor' => $formatterConstructor,
        ];
    }

    protected function getDefaultHandlerConfig($config)
    {
        $handlerClass = Arr::get($config, 'handler.class', StreamHandler::class);
        $handlerConstructor = Arr::get($config, 'handler.constructor', [
            'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
            'level' => Logger::DEBUG,
        ]);

        return [
            'class' => $handlerClass,
            'constructor' => $handlerConstructor,
        ];
    }

    protected function processors(array $config): array
    {
        $result = [];
        if (! isset($config['processors']) && isset($config['processor'])) {
            $config['processors'] = [$config['processor']];
        }

        foreach ($config['processors'] ?? [] as $value) {
            if (is_array($value) && isset($value['class'])) {
                $value = make($value['class'], $value['constructor'] ?? []);
            }

            $result[] = $value;
        }

        return $result;
    }

    protected function handlers(array $config): array
    {
        $handlerConfigs = $config['handlers'] ?? [[]];
        $handlers = [];
        $defaultHandlerConfig = $this->getDefaultHandlerConfig($config);
        $defaultFormatterConfig = $this->getDefaultFormatterConfig($config);
        foreach ($handlerConfigs as $value) {
            $class = $value['class'] ?? $defaultHandlerConfig['class'];
            $constructor = $value['constructor'] ?? $defaultHandlerConfig['constructor'];
            if (isset($value['formatter'])) {
                if (! isset($value['formatter']['constructor'])) {
                    $value['formatter']['constructor'] = $defaultFormatterConfig['constructor'];
                }
            }
            $formatterConfig = $value['formatter'] ?? $defaultFormatterConfig;

            $handlers[] = $this->handler($class, $constructor, $formatterConfig);
        }

        return $handlers;
    }

    protected function handler($class, $constructor, $formatterConfig): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = make($class, $constructor);

        if ($handler instanceof FormattableHandlerInterface) {
            $formatterClass = $formatterConfig['class'];
            $formatterConstructor = $formatterConfig['constructor'];

            /** @var FormatterInterface $formatter */
            $formatter = make($formatterClass, $formatterConstructor);

            $handler->setFormatter($formatter);
        }

        return $handler;
    }
}
