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
use Hyperf\HttpServer\Server;
use Psr\Container\ContainerInterface;
use function trackerHookMalloc;

/**
 * @deprecated v2.1 use HookMallocMiddleware instead.
 */
class OnRequestAspect extends AbstractAspect
{
    public $classes = [
        Server::class . '::onRequest',
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
        if (function_exists('trackerHookMalloc')) {
            trackerHookMalloc();
        }
        return $proceedingJoinPoint->process();
    }
}
