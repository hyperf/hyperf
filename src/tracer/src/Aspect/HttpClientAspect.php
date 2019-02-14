<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Aspect;

use GuzzleHttp\Client;
use Hyperf\Tracer\Tracing;
use Zipkin\Propagation\Map;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class HttpClientAspect implements ArroundInterface
{
    public $classes = [
        Client::class . '::requestAsync',
    ];

    public $annotations = [];

    /**
     * @var Tracing
     */
    private $tracing;

    /**
     * @var SwitchManager
     */
    private $switchManager;

    public function __construct(Tracing $tracing, SwitchManager $switchManager)
    {
        $this->tracing = $tracing;
        $this->switchManager = $switchManager;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->switchManager->isEnable('guzzle') === false) {
            return $proceedingJoinPoint->process();
        }
        $options = $proceedingJoinPoint->arguments['keys']['options'];
        if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
            return $proceedingJoinPoint->process();
        }
        $span = $this->tracing->span('guzzlehttp.request', \Zipkin\Kind\CLIENT);
        $span->tag('source', $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);
        $appendHeaders = [];
        // Injects the context into the wire
        $injector = $this->tracing->getPropagation()->getInjector(new Map());
        $injector($span->getContext(), $appendHeaders);
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options'] = $options;
        $span->start();
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}
