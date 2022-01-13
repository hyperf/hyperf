<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Elasticsearch\Client;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use OpenTracing\Tracer;

class ElasticserachAspect extends AbstractAspect
{
    use SpanStarter;

    /**
     * @var array
     */
    public $classes
        = [
            Client::class . '::__construct',
            Client::class . '::bulk',
            Client::class . '::count',
            Client::class . '::create',
            Client::class . '::get',
            Client::class . '::getSource',
            Client::class . '::index',
            Client::class . '::mget',
            Client::class . '::msearch',
            Client::class . '::scroll',
            Client::class . '::search',
            Client::class . '::update',
            Client::class . '::updateByQuery',
            Client::class . '::search',
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
        if ($this->switchManager->isEnable('elasticsearch') === false) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $span = $this->startSpan('Elasticsearch' . '::' . $arguments['name']);
        $span->setTag( $arguments['name'], json_encode($arguments['arguments']));
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}
