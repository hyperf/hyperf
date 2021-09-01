<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Logger;

use Hyperf\Utils\Context;
use Psr\Log\LoggerInterface;

class XxlJobHelper
{
    /**
     * @var XxlJobLogger
     */
    private $xxlJobLogger;

    public function __construct(XxlJobLogger $xxlJobLogger)
    {
        $this->xxlJobLogger = $xxlJobLogger;
    }

    public function log($message)
    {
        if (empty(Context::get(XxlJobLogger::MARK_JOB_LOG_ID))) {
            return;
        }
        $this->xxlJobLogger->get()->info($message);
    }

    public function get(): ?LoggerInterface
    {
        if (empty(Context::get(XxlJobLogger::MARK_JOB_LOG_ID))) {
            return null;
        }
        return $this->xxlJobLogger->get();
    }

    public function logFile(): string
    {
        return $this->xxlJobLogger->getStream()->getTimedFilename();
    }
}
