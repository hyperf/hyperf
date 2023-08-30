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
namespace Hyperf\Tracer\Adapter\Reporter;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Zipkin\Reporter;

use function Hyperf\Support\make;

class ReporterFactory
{
    public function __construct(
        private ContainerInterface $container,
        private HttpClientFactory $httpClientFactory
    ) {
    }

    public function make(array $option = []): Reporter
    {
        $class = $option['class'] ?? '';
        $constructor = $option['constructor'] ?? [];

        if ($class === \Zipkin\Reporters\Http::class) {
            $constructor['requesterFactory'] = $this->httpClientFactory;
        }

        if (isset($constructor['logger'])) {
            $loggerClass = (string) $constructor['logger'];
            if (
                ! $this->container->has($loggerClass)
                || ! ($constructor['logger'] = $this->container->get($loggerClass)) instanceof LoggerInterface
            ) {
                unset($constructor['logger']);
            }
        }

        if (! class_exists($class)) {
            throw new RuntimeException(sprintf('Class %s is not exists.', $class));
        }

        if (! is_a($class, Reporter::class, true)) {
            throw new RuntimeException('Unsupported reporter.');
        }

        return make($class, $constructor);
    }
}
