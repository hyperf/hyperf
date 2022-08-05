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

use Doctrine\Common\Annotations\Reader;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeRegistry;

class RecursiveTypeMapperFactory
{
    public function __construct(private ContainerInterface $container, private CacheInterface $cache, private NamingStrategyInterface $namingStrategy, private TypeRegistry $typeRegistry)
    {
    }

    public function __invoke()
    {
        $typeMappers = [];
        $annotationReader = new AnnotationReader($this->container->get(Reader::class), AnnotationReader::LAX_MODE);
        $typeGenerator = $this->container->get(TypeGenerator::class);
        $inputTypeGenerator = $this->container->get(InputTypeGenerator::class);
        $inputTypeUtils = $this->container->get(InputTypeUtils::class);
        $namingStrategy = $this->container->get(NamingStrategyInterface::class);
        $lockStore = new FlockStore(sys_get_temp_dir());
        $lockFactory = new LockFactory($lockStore);

        $typeMappers[] = new TypeMapper(
            'app',
            $typeGenerator,
            $inputTypeGenerator,
            $inputTypeUtils,
            $this->container,
            $annotationReader,
            $namingStrategy,
            $lockFactory,
            $this->cache
        );
        $typeMappers[] = new PorpaginasTypeMapper();
        $compositeTypeMapper = new CompositeTypeMapper($typeMappers);
        return new RecursiveTypeMapper($compositeTypeMapper, $this->namingStrategy, $this->cache, $this->typeRegistry);
    }
}
