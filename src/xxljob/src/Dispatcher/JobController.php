<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Dispatcher;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\XxlJob\Application;
use Hyperf\XxlJob\Handler\AbstractJobHandler;
use Hyperf\XxlJob\Logger\XxlJobLogger;
use Hyperf\XxlJob\Requests\LogRequest;
use Hyperf\XxlJob\Requests\RunRequest;
use Throwable;

class JobController extends BaseJobController
{
    public function run(): ResponseInterface
    {
        $runRequest = RunRequest::create($this->input());
        if ($runRequest->getGlueType() != 'BEAN') {
            return $this->resultJson($this->fail['msg'] = 'the client only supports BEAN');
        }
        $executorHandler = $runRequest->getExecutorHandler();
        $className = Application::getJobHandlers($executorHandler);

        if (empty($className)) {
            return $this->resultJson($this->fail['msg'] = 'executorHandler:' . $executorHandler . ' class not found!');
        }

        $stdoutLogger = $this->container->get(StdoutLoggerInterface::class);
        $jobHandlerObj = $this->container->get($className);
        if (! $jobHandlerObj instanceof AbstractJobHandler) {
            $message = 'XxlJob:' . $className . ' not instanceof AbstractJobHandler';
            $stdoutLogger->error($message);
            return $this->resultJson($this->fail['msg'] = $message);
        }
        $jobHandlerObj->setRunRequest($runRequest);

        Coroutine::create(function () use ($jobHandlerObj, $runRequest) {
            $this->handle($jobHandlerObj, $runRequest);
        });
        return $this->resultJson($this->success);
    }

    public function log(): ResponseInterface
    {
        $logRequest = LogRequest::create($this->input());

        $logFile = $this->getXxlJobHelper()->logFile();

        if (! file_exists($logFile)) {
            $data = [
                'code' => 200,
                'msg' => null,
                'content' => [
                    'fromLineNum' => $logRequest->getFromLineNum(),
                    'toLineNum' => 0,
                    'logContent' => 'readLog fail, logFile not exists',
                    'isEnd' => true,
                ],
            ];
            return $this->resultJson($data);
        }

        [$content,$row] = $this->getXxlJobLogger()->getLine($logFile, $logRequest->getFromLineNum());
        $data = [
            'code' => 200,
            'msg' => null,
            'content' => [
                'fromLineNum' => $logRequest->getFromLineNum(),
                'toLineNum' => $row,
                'logContent' => $content,
                'isEnd' => false,
            ],
        ];

        return $this->resultJson($data);
    }

    public function beat(): ResponseInterface
    {
        return $this->resultJson($this->success);
    }

    public function idleBeat(): ResponseInterface
    {
        return $this->resultJson($this->success);
    }

    public function kill(): ResponseInterface
    {
        return $this->resultJson($this->fail['msg'] = 'not supported !');
    }

    /**
     * @throws Throwable
     */
    private function handle(AbstractJobHandler $jobHandlerObj, RunRequest $runRequest)
    {
        //set
        Context::set(XxlJobLogger::MARK_JOB_LOG_ID, $runRequest->getLogId());
        /*$server = $this->serverFactory->getServer()->getServer();
        $workerId = $server->getWorkerId();
        $cid = Coroutine::id();
        $this->getXxlJobHelper()->log("----------- workId:{$workerId} cid:{$cid} -----------");*/
        //log
        $this->getXxlJobHelper()->log('----------- php xxl-job job execute start -----------');
        $this->getXxlJobHelper()->log('----------- param:' . $runRequest->getExecutorParams());

        try {
            $jobHandlerObj->handle();
            $this->getXxlJobHelper()->log('----------- php xxl-job job execute end(finish) -----------');
        } catch (Throwable $throwable) {
            $message = $throwable->getMessage();
            if ($this->container->has(FormatterInterface::class)) {
                $formatter = $this->container->get(FormatterInterface::class);
                $message = $formatter->format($throwable);
                $message = str_replace("\n", '<br>', $message);
            }
            $this->getXxlJobHelper()->get()->error($message);
            $this->app->service->callback($runRequest->getLogId(), $runRequest->getLogDateTime(), 500, $message);
            throw $throwable;
        }
        $this->app->service->callback($runRequest->getLogId(), $runRequest->getLogDateTime());
    }
}
