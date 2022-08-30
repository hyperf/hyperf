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

use function getSwooleTrackerSpanId;
use function getSwooleTrackerTraceId;

class CoroutineHandlerAspect extends AbstractAspect
{
    public array $classes = [
        CoroutineHandler::class . '::execute',
    ];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (class_exists(Stats::class) && $client = $proceedingJoinPoint->getArguments()[0] ?? null) {
            if ($client instanceof Client && function_exists('getSwooleTrackerTraceId') && function_exists('getSwooleTrackerSpanId')) {
                $client->setHeaders(array_merge(
                    [
                        'x-swoole-traceid' => getSwooleTrackerTraceId(),
                        'x-swoole-spanid' => getSwooleTrackerSpanId(),
                    ],
                    $client->requestHeaders
                ));
            }
        }
        return $proceedingJoinPoint->process();
    }
}
