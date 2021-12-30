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

use Hyperf\DB\DB;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use OpenTracing\Tracer;

class DbAspect extends AbstractAspect
{
    use SpanStarter;

    /**
     * @var array
     */
    public $classes = [
        DB::class . '::__call',
    ];

    /**
     * @var array
     */
    public $annotations = [];

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

    public function __construct(Tracer $tracer, SwitchManager $switchManager, SpanTagManager $spanTagManager)
    {
        $this->tracer = $tracer;
        $this->switchManager = $switchManager;
        $this->spanTagManager = $spanTagManager;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->switchManager->isEnable('db') === false) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $span = $this->startSpan('Db' . '::' . $arguments['name']);
        $span->setTag($this->spanTagManager->get('db', 'db.query'), json_encode($arguments['arguments']));
        try {
            $result = $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            $span->setTag('error', true);
            $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            $span->finish();
        }
        return $result;
    }
}
