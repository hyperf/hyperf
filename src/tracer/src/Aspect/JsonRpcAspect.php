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
namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc\Context;
use Hyperf\RpcClient\AbstractServiceClient;
use Hyperf\RpcClient\Client;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Utils\Context as CT;
use OpenTracing\Tracer;
use Psr\Container\ContainerInterface;
use Zipkin\Span;
use const OpenTracing\Formats\TEXT_MAP;

class JsonRpcAspect implements AroundInterface
{
    use SpanStarter;

    public $classes = [
        AbstractServiceClient::class . '::__generateRpcPath',
        Client::class . '::send',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Tracer
     */
    private $tracer;

    /**
     * @var SwitchManager
     */
    private $switchManager;

    /**
     * @var SpanTagManager
     */
    private $spanTagManager;

    /**
     * @var Context
     */
    private $context;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->tracer = $container->get(Tracer::class);
        $this->switchManager = $container->get(SwitchManager::class);
        $this->spanTagManager = $container->get(SpanTagManager::class);
        $this->context = $container->get(Context::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->methodName === '__generateRpcPath') {
            $path = $proceedingJoinPoint->process();
            $key = "JsonRPC send [{$path}]";
            $span = $this->startSpan($key);
            if ($this->spanTagManager->has('rpc', 'path')) {
                $span->setTag($this->spanTagManager->get('rpc', 'path'), $path);
            }
            $carrier = [];
            // Injects the context into the wire
            $this->tracer->inject(
                $span->getContext(),
                TEXT_MAP,
                $carrier
            );
            $this->context->set('tracer.carrier', $carrier);
            CT::set('tracer.span.' . static::class, $span);
            return $path;
        }

        if ($proceedingJoinPoint->methodName === 'send') {
            try {
                $result = $proceedingJoinPoint->process();
            } catch (\Throwable $e) {
                if ($span = CT::get('tracer.span.' . static::class)) {
                    $span->setTag('error', true);
                    $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
                    CT::set('tracer.span.' . static::class, $span);
                }
                throw $e;
            } finally {
                /** @var Span $span */
                if ($span = CT::get('tracer.span.' . static::class)) {
                    if ($this->spanTagManager->has('rpc', 'status')) {
                        $span->setTag($this->spanTagManager->get('rpc', 'status'), isset($result['result']) ? 'OK' : 'Failed');
                    }
                    $span->finish();
                }
            }

            return $result;
        }
        return $proceedingJoinPoint->process();
    }
}
