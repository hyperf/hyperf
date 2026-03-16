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

namespace Hyperf\AsyncQueue;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;

class AnnotationJob extends Job
{
    public array $params = [];

    public function __construct(public string $class, public string $method, array $params, public int $maxAttempts = 0)
    {
        foreach ($params as $key => $value) {
            if ($value instanceof CompressInterface) {
                $value = $value->compress();
            }
            $this->params[$key] = $value;
        }
    }

    public function handle()
    {
        $container = ApplicationContext::getContainer();
        $instance = $container->get($this->class);
        $params = [];

        foreach ($this->params as $key => $value) {
            if ($value instanceof UnCompressInterface) {
                $value = $value->uncompress();
            }
            $params[$key] = $value;
        }

        $container->get(Environment::class)->setAsyncQueue(true);

        $method = $this->method;

        (fn () => $this->{$method}(...$params))->call($instance);
    }
}
