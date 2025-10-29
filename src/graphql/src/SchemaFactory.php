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

namespace Hyperf\GraphQL;

use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\SchemaFactory as BaseSchemaFactory;

class SchemaFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $factory = new BaseSchemaFactory($container->get(CacheInterface::class), $container);
        $factory->addTypeNamespace('App');
        $factory->addControllerNamespace('App');
        $factory->setDoctrineAnnotationReader($container->get(Reader::class));

        return $factory->createSchema();
    }
}
