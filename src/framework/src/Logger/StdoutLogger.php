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

namespace Hyperf\Framework\Logger;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use function sprintf;
use function str_replace;

/**
 * Default logger for logging server start and requests.
 * PSR-3 logger implementation that logs to STDOUT, using a newline after each
 * message. Priority is ignored.
 */
class StdoutLogger implements StdoutLoggerInterface
{
    /**
     * @var \Hyperf\Contracts\ConfigInterface
     */
    private $config;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->output = new ConsoleOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        $config = $this->config->get(StdoutLoggerInterface::class, ['log_level' => []]);
        if (! in_array($level, $config['log_level'])) {
            return;
        }
        $search = array_map(function ($key) {
            return sprintf('{%s}', $key);
        }, array_keys($context));
        $message = str_replace($search, $context, $message);

        $this->output->writeln($this->getMessage($message, $level));
    }

    protected function getMessage($message, $level = LogLevel::INFO)
    {
        $tag = null;
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                $tag = 'error';
                break;
            case LogLevel::ERROR:
                $tag = 'fg=red';
                break;
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                $tag = 'comment';
                break;
            case LogLevel::INFO:
                $tag = 'info';
                break;
            default:
                return sprintf('[%s] %s', strtoupper($level), $message);
        }

        return sprintf('<%s>[%s] %s</>', $tag, strtoupper($level), $message);
    }
}
