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
namespace Hyperf\Tracer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Rpc;
use Hyperf\Tracer\Adapter\ZipkinTracerFactory;
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use Hyperf\Tracer\Exception\InvalidArgumentException;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Psr\Http\Message\ServerRequestInterface;
use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

trait SpanStarter
{
    public function getTracer(): Tracer
    {
        if (! Context::has(Tracer::class)) {
            Context::set(Tracer::class, $this->initTracer());
        }
        return Context::get(Tracer::class);
    }

    /**
     * Helper method to start a span while setting context.
     */
    protected function startSpan(
        string $name,
        array $option = [],
        string $kind = SPAN_KIND_RPC_SERVER
    ): Span {
        $tracer = $this->getTracer();
        $root = Context::get('tracer.root');
        if (! $root instanceof Span) {
            $container = ApplicationContext::getContainer();
            /** @var ServerRequestInterface $request */
            $request = Context::get(ServerRequestInterface::class);
            if (! $request instanceof ServerRequestInterface) {
                // If the request object is absent, we are probably in a commandline context.
                // Throwing an exception is unnecessary.
                $root = $tracer->startSpan($name, $option);
                $root->setTag(SPAN_KIND, $kind);
                Context::set('tracer.root', $root);
                return $root;
            }
            $carrier = array_map(function ($header) {
                return $header[0];
            }, $request->getHeaders());
            if ($container->has(Rpc\Context::class) && $rpcContext = $container->get(Rpc\Context::class)) {
                $rpcCarrier = $rpcContext->get('tracer.carrier');
                if (! empty($rpcCarrier)) {
                    $carrier = $rpcCarrier;
                }
            }
            // Extracts the context from the HTTP headers.
            $spanContext = $tracer->extract(TEXT_MAP, $carrier);
            if ($spanContext) {
                $option['child_of'] = $spanContext;
            }
            $root = $tracer->startSpan($name, $option);
            $root->setTag(SPAN_KIND, $kind);
            Context::set('tracer.root', $root);
            return $root;
        }
        $option['child_of'] = $root->getContext();
        $child = $tracer->startSpan($name, $option);
        $child->setTag(SPAN_KIND, $kind);
        return $child;
    }

    private function initTracer(): Tracer
    {
        $container = ApplicationContext::getContainer();
        $config = $container->get(ConfigInterface::class);
        $name = $config->get('opentracing.default');

        // v1.0 has no 'default' config. Fallback to v1.0 mode for backward compatibility.
        if (empty($name)) {
            $factory = $container->get(ZipkinTracerFactory::class);
            return $factory->make('');
        }

        $driver = $config->get("opentracing.tracer.{$name}.driver");
        if (empty($driver)) {
            throw new InvalidArgumentException(
                sprintf('The tracing config [%s] doesn\'t contain a valid driver.', $name)
            );
        }

        $factory = $container->get($driver);

        if (! ($factory instanceof NamedFactoryInterface)) {
            throw new InvalidArgumentException(
                sprintf('The driver %s is not a valid factory.', $driver)
            );
        }

        return $factory->make($name);
    }
}
