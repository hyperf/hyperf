<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Utils;

class JobFileHandler extends StreamHandler
{
    /**
     * @var int
     */
    private $logId;

    /**
     * @var string
     */
    private $filename;

    public function __construct(int $logId, string $filename,$level = Logger::DEBUG, bool $bubble = true, ?int $filePermission = null, bool $useLocking = false)
    {
        $this->filename = Utils::canonicalizePath($filename);
        $this->logId = $logId;
        parent::__construct($this->getTimedFilename(), $level, $bubble, $filePermission, $useLocking);
    }

    public function rotate($nextMaxDayTime): void
    {
        $logFiles = glob($this->getGlobPattern());
        if ($logFiles === false) {
            // failed to glob
            return;
        }
        asort($logFiles, SORT_STRING | SORT_FLAG_CASE | SORT_NATURAL);
        $i = 0;
        foreach ($logFiles as $file) {
            if (is_writable($file)) {
                $mtime = filemtime($file);
                if ($mtime < $nextMaxDayTime) {
                    // suppress errors here as unlink() might fail if two processes
                    // are cleaning up/rotating at the same time
                    set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
                        return false;
                    });
                    unlink($file);
                    restore_error_handler();
                } else {
                    ++$i;
                    if ($i > 5) {
                        break;
                    }
                }
            }
        }
    }

    public function getTimedFilename(): string
    {
        $fileInfo = pathinfo($this->filename);
        $jogId = $this->getLogId() ?: 'job';
        return $fileInfo['dirname'] . '/' . $jogId . '.log';
    }


    protected function getGlobPattern(): string
    {
        $fileInfo = pathinfo($this->filename);
        return $fileInfo['dirname'] . '/[0-9]*.log';
    }

    private function getLogId(): ?int
    {
        return $this->logId;
    }
}
