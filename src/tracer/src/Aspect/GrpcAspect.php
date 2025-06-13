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

class GrpcAspect extends AbstractAspect
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
        if (! $this->switchManager->isEnable('grpc')) {
            return $proceedingJoinPoint->process();
        }

        return match ($proceedingJoinPoint->methodName) {
            'send' => $this->processSend($proceedingJoinPoint),
            'recv' => $this->processRecv($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function processSend(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->getArguments();
        /** @var Request $request */
        $request = $arguments[0];
        $key = "GRPC send [{$request->path}]";
        $span = $this->startSpan($key);
        $carrier = [];
        // Injects the context into the wire
        TracerContext::getTracer()->inject(
            $span->getContext(),
            TEXT_MAP,
            $carrier
        );

        // Merge tracer info
        $request->headers = array_merge($request->headers, $carrier);
        if ($this->spanTagManager->has('grpc', 'request.header')) {
            foreach ($request->headers as $key => $value) {
                $span->setTag($this->spanTagManager->get('grpc', 'request.header') . '.' . $key, $value);
            }
        }

        $this->context->set('tracer.carrier', $carrier);
        CT::set('tracer.span.' . static::class, $span);

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            if ($this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e)) {
                $span->setTag('error', true);
                $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            }
            throw $e;
        }
    }

    private function processRecv(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var null|Span $span */
        $span = CT::get('tracer.span.' . static::class);

        try {
            /** @var bool|Response $result */
            $result = $proceedingJoinPoint->process();
            if ($result instanceof Response) {
                if ($this->spanTagManager->has('grpc', 'response.header')) {
                    foreach ($result->headers as $key => $value) {
                        $span?->setTag($this->spanTagManager->get('grpc', 'response.header') . '.' . $key, $value);
                    }
                }
            }
        } catch (Throwable $e) {
            if ($this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e::class)) {
                $span?->setTag('error', true);
                $span?->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            }
            throw $e;
        } finally {
            $span?->finish();
        }

        return $result;
    }
}
