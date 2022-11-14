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

use Hyperf\Context\Context as CT;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Rpc\Context;
use Hyperf\RpcClient\AbstractServiceClient;
use Hyperf\RpcClient\Client;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Psr\Container\ContainerInterface;
use Throwable;

use const OpenTracing\Formats\TEXT_MAP;

class JsonRpcAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        AbstractServiceClient::class . '::__generateRpcPath',
        Client::class . '::send',
    ];

    private Tracer $tracer;

    private SwitchManager $switchManager;

    private SpanTagManager $spanTagManager;

    private Context $context;

    public function __construct(private ContainerInterface $container)
    {
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
            } catch (Throwable $e) {
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
