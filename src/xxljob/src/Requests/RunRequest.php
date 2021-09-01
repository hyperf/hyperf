<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Requests;

class RunRequest extends BaseRequest
{
    protected $jobId;  // 任务ID

    protected $executorHandler; // 任务标识

    protected $executorParams; // 任务参数

    protected $executorBlockStrategy; // 任务阻塞策略，可选值参考 com.xxl.job.core.enums.ExecutorBlockStrategyEnum

    protected $executorTimeout; // 任务超时时间，单位秒，大于零时生效

    protected $logId; // 本次调度日志ID

    protected $logDateTime; // 本次调度日志时间

    protected $glueType; // 任务模式，可选值参考 com.xxl.job.core.glue.GlueTypeEnum

    protected $glueSource;  // GLUE脚本代码

    protected $glueUpdatetime; // GLUE脚本更新时间，用于判定脚本是否变更以及是否需要刷新

    protected $broadcastIndex; // 分片参数：当前分片

    protected $broadcastTotal; // 分片参数：总分片

    public function getJobId(): int
    {
        return $this->jobId;
    }

    public function getExecutorHandler(): string
    {
        return $this->executorHandler;
    }

    public function getExecutorParams(): string
    {
        return $this->executorParams;
    }

    public function getExecutorBlockStrategy(): string
    {
        return $this->executorBlockStrategy;
    }

    public function getExecutorTimeout(): int
    {
        return $this->executorTimeout;
    }

    public function getLogId(): int
    {
        return $this->logId;
    }

    public function getLogDateTime(): int
    {
        return $this->logDateTime;
    }

    public function getGlueType(): string
    {
        return $this->glueType;
    }

    public function getGlueSource(): string
    {
        return $this->glueSource;
    }

    public function getGlueUpdatetime(): string
    {
        return $this->glueUpdatetime;
    }

    public function getBroadcastIndex(): int
    {
        return $this->broadcastIndex;
    }

    public function getBroadcastTotal(): int
    {
        return $this->broadcastTotal;
    }
}
