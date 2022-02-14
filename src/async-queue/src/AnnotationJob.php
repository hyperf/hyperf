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

use Hyperf\Contract\CompressInterface;
use Hyperf\Contract\UnCompressInterface;
use Hyperf\Utils\ApplicationContext;

class AnnotationJob extends Job
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $params = [];

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

        $env = $container->get(Environment::class);
        $env->setAsyncQueue(true);

        $class->{$this->method}(...$params);
    }
}
