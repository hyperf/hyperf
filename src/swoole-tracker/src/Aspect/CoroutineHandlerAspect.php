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
namespace Hyperf\SwooleTracker\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Guzzle\CoroutineHandler;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Http\Client;
use SwooleTracker\Stats;

class CoroutineHandlerAspect extends AbstractAspect
{
    public $classes = [
        CoroutineHandler::class . '::execute',
    ];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (class_exists(Stats::class) && $client = $proceedingJoinPoint->getArguments()[0] ?? null) {
            if ($client instanceof Client) {
                $client->setHeaders(array_merge(
                    [
                        'x-swoole-traceid' => '', // TODO: 获取生成的 TraceId
                        'x-swoole-spanid' => '',
                    ],
                    $client->requestHeaders
                ));
            }
        }
        return $proceedingJoinPoint->process();
    }
}
