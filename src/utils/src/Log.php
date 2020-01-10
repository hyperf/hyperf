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

namespace Hyperf\Utils;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;

class Log
{
    public static function emergency($message, array $context = [])
    {
        return self::log('emergency', $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        return self::log('alert', $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        return self::log('critical', $message, $context);
    }

    public static function error($message, array $context = [])
    {
        return self::log('error', $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        return self::log('warning', $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        return self::log('notice', $message, $context);
    }

    public static function info($message, array $context = [])
    {
        return self::log('info', $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        return self::log('debug', $message, $context);
    }

    public static function log($level, $message, array $context = []): void
    {
        if (ApplicationContext::hasContainer() && $container = ApplicationContext::getContainer()) {
            if ($container->has(StdoutLoggerInterface::class) && $logger = $container->get(StdoutLoggerInterface::class)) {
                if (! $message instanceof \Throwable) {
                    $logger->log($level, $message, $context);
                    return;
                }

                if ($container->has(FormatterInterface::class) && $formatter = $container->get(FormatterInterface::class)) {
                    $logger->log($level, $formatter->format($message), $context);
                    return;
                }

                $logger->log($level, (string) $message, $context);
                return;
            }
        }

        $contextStr = '';
        foreach ($context as $key => $value) {
            $contextStr .= ' ' . $key . ':' . $value . ',';
        }
        echo sprintf('[%s] %s [%s]', strtoupper($level), (string) $message, rtrim($contextStr, ',')) . PHP_EOL;
    }
}
