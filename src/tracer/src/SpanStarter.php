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

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Rpc;
use OpenTracing\Span;
use Psr\Http\Message\ServerRequestInterface;

use const OpenTracing\Formats\TEXT_MAP;
use const OpenTracing\Tags\SPAN_KIND;
use const OpenTracing\Tags\SPAN_KIND_RPC_SERVER;

trait SpanStarter
{
    /**
     * Helper method to start a span while setting context.
     */
    protected function startSpan(
        string $name,
        array $option = [],
        string $kind = SPAN_KIND_RPC_SERVER
    ): Span {
        $root = Context::get('tracer.root');
        if (! $root instanceof Span) {
            $container = ApplicationContext::getContainer();
            /** @var ServerRequestInterface $request */
            $request = Context::get(ServerRequestInterface::class);
            if (! $request instanceof ServerRequestInterface) {
                // If the request object is absent, we are probably in a commandline context.
                // Throwing an exception is unnecessary.
                $root = $this->tracer->startSpan($name, $option);
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
            $spanContext = $this->tracer->extract(TEXT_MAP, $carrier);
            if ($spanContext) {
                $option['child_of'] = $spanContext;
            }
            $root = $this->tracer->startSpan($name, $option);
            $root->setTag(SPAN_KIND, $kind);
            Context::set('tracer.root', $root);
            return $root;
        }
        $option['child_of'] = $root->getContext();
        $child = $this->tracer->startSpan($name, $option);
        $child->setTag(SPAN_KIND, $kind);
        return $child;
    }
}
