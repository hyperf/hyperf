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
    public string $class;

    public string $method;

    public array $params = [];

    public function __construct(string $class, string $method, array $params, int $maxAttempts = 0)
    {
        $this->class = $class;
        $this->method = $method;
        $this->maxAttempts = $maxAttempts;
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

        $class = $container->get($this->class);

        $params = [];
        foreach ($this->params as $key => $value) {
            if ($value instanceof UnCompressInterface) {
                $value = $value->uncompress();
            }
            $params[$key] = $value;
        }

        $container->get(Environment::class)->setAsyncQueue(true);

        $class->{$this->method}(...$params);
    }
}
