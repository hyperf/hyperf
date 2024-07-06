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
use Hyperf\Tracer\Adapter\ZipkinTracerFactory;
use Hyperf\Tracer\Contract\NamedFactoryInterface;
use Hyperf\Tracer\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class TracerFactory
{
    public function __invoke(ContainerInterface $container)
    {
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
                sprintf('The tracing config [%s] does not contain a valid driver.', $name)
            );
        }

        $factory = $container->get($driver);

        if (! $factory instanceof NamedFactoryInterface) {
            throw new InvalidArgumentException(
                sprintf('The driver %s is not a valid factory.', $driver)
            );
        }

        return $factory->make($name);
    }
}
