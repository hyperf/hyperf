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
use Hyperf\GrpcClient\GrpcClient;
use Hyperf\GrpcClient\Request;
use Hyperf\Rpc\Context;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Psr\Container\ContainerInterface;
use Swoole\Http2\Response;
use Throwable;

use const OpenTracing\Formats\TEXT_MAP;

class GrpcClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        GrpcClient::class . '::send',
        GrpcClient::class . '::recv',
    ];

    private SwitchManager $switchManager;

    private SpanTagManager $spanTagManager;

    private Context $context;

    public function __construct(private ContainerInterface $container)
    {
        $this->switchManager = $container->get(SwitchManager::class);
        $this->spanTagManager = $container->get(SpanTagManager::class);
        $this->context = $container->get(Context::class);
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($proceedingJoinPoint->methodName === 'send') {
            // request start
            $arguments = $proceedingJoinPoint->getArguments();
            $request = $arguments[0] ?? '';
            /* @var Request $request */
            $key = "GRPCClient send [{$request->path}]";
            $span = $this->startSpan($key);
            $carrier = [];
            // Injects the context into the wire
            TracerContext::getTracer()->inject(
                $span->getContext(),
                TEXT_MAP,
                $carrier
            );
            // merge tracer info
            $request->headers = array_merge($request->headers, $carrier);
            if ($this->spanTagManager->has('grpc_client', 'request.header')) {
                foreach ($request->headers as $headerKey => $headerValue) {
                    $span->setTag($this->spanTagManager->get('grpc_client', 'request.header') . '.' . $headerKey, $headerValue);
                }
            }

            $this->context->set('tracer.carrier', $carrier);
            CT::set('tracer.span.' . static::class, $span);

            try {
                return $proceedingJoinPoint->process();
            } catch (Throwable $e) {
                if (($span = CT::get('tracer.span.' . static::class)) && $this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e::class)) {
                    $span->setTag('error', true);
                    $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
                    CT::set('tracer.span.' . static::class, $span);
                }
                throw $e;
            }
        }
        if ($proceedingJoinPoint->methodName === 'recv') {
            // request end
            try {
                $result = $proceedingJoinPoint->process();
            } catch (Throwable $e) {
                if (($span = CT::get('tracer.span.' . static::class)) && $this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e::class)) {
                    $span->setTag('error', true);
                    $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
                    CT::set('tracer.span.' . static::class, $span);
                }
                throw $e;
            } finally {
                /** @var Span $span */
                if ($span = CT::get('tracer.span.' . static::class)) {
                    if ($result instanceof Response) {
                        if ($this->spanTagManager->has('grpc_client', 'response.header')) {
                            /* @var \Swoole\Http2\Response $result */
                            foreach ($result->headers as $headerKey => $headerValue) {
                                $span->setTag($this->spanTagManager->get('grpc_client', 'response.header') . '.' . $headerKey, $headerValue);
                            }
                        }
                    }
                    $span->finish();
                }
            }

            return $result;
        }

        return $proceedingJoinPoint->process();
    }
}
