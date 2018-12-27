<?php

namespace Hyperflex\Framework\Logger;


use Hyperflex\Contract\ConfigInterface;
use Hyperflex\Framework\Contracts\StdoutLoggerInterface;
use Psr\Log\LogLevel;

use function printf;
use function sprintf;
use function str_replace;

use const PHP_EOL;

/**
 * Default logger for logging server start and requests.
 * PSR-3 logger implementation that logs to STDOUT, using a newline after each
 * message. Priority is ignored.
 */
class StdoutLogger implements StdoutLoggerInterface
{

    /**
     * @var \Hyperflex\Contracts\ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $config = $this->config->get(StdoutLoggerInterface::class, ['log_level' => []]);
        if (! in_array($level, $config['log_level'])) {
            return;
        }
        foreach ($context as $key => $value) {
            $search = sprintf('{%s}', $key);
            $message = str_replace($search, $value, $message);
        }
        printf('%s%s', $message, PHP_EOL);
    }

}