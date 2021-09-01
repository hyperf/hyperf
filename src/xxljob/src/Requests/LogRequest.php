<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Requests;

class LogRequest extends BaseRequest
{
    protected $logDateTim;

    protected $logId;

    protected $fromLineNum;

    public function getLogDateTim(): int
    {
        return $this->logDateTim;
    }

    public function getLogId(): int
    {
        return $this->logId;
    }

    public function getFromLineNum(): int
    {
        return $this->fromLineNum;
    }
}
