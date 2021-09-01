<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Logger;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Context;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SplFileObject;

class XxlJobLogger
{
    public const MARK_JOB_LOG_ID = 'XXL-JOB-CONTEXT-LOG-ID';

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $maxDay;

    /**
     * @var JobFileHandler
     */
    private $stream;

    /**
     * @var int
     */
    private $nextMaxDayTime = 0;

    public function __construct(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $this->filename = $config->get('xxl_job.log.filename', BASE_PATH . '/runtime/logs/xxl-job/job.log');
        $this->maxDay = $config->get('xxl_job.log.maxDay', 30);
    }

    public function getStream(): JobFileHandler
    {
        $this->get();
        return $this->stream;
    }

    public function get(): LoggerInterface
    {
        return Context::override(self::class, function () {
            $logId = Context::get(XxlJobLogger::MARK_JOB_LOG_ID);
            return $this->make($logId);
        });
    }

    public function getLine($fileName, $start): array
    {
        $file = new SplFileObject($fileName, 'r');
        $file->seek($start - 1);
        $content = '';
        while (! $file->eof()) {
            $current = $file->current();
            if (empty($current)) {
                break;
            }
            $content .= $current;
            $file->next();
        }
        $row = $file->key();
        //Closing file object
        $file = null;
        return [$content, $row];
    }

    protected function make($logId): Logger
    {
        $log = new Logger('xxl-job-log');
        $this->stream = new JobFileHandler($logId, $this->filename,Logger::DEBUG);
        $fire = new FirePHPHandler();
        $dateFormat = 'Y-m-d H:i:s';
        $output = "%datetime% [%level_name%]: %message%\n";
        $formatter = new LineFormatter($output, $dateFormat);
        $this->stream->setFormatter($formatter);
        $log->pushHandler($this->stream);
        $log->pushHandler($fire);
        $maxDayTime = $this->getMaxDayTime();
        if($this->maxDay > 0 && $this->nextMaxDayTime != $maxDayTime){
            $this->stream->rotate($maxDayTime);
            $this->nextMaxDayTime = $maxDayTime;
        }
        return $log;
    }

    protected function getMaxDayTime(): int
    {
        $time = sprintf('-%s days', $this->maxDay);
        $date = date('Y-m-d 00:00:00', strtotime($time));
        return strtotime($date);
    }

}
