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
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\TypeRegistry;

class RecursiveTypeMapperFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    public function __construct(ContainerInterface $container, CacheInterface $cache, NamingStrategyInterface $namingStrategy, TypeRegistry $typeRegistry)
    {
        $this->cache = $cache;
        $this->container = $container;
        $this->namingStrategy = $namingStrategy;
        $this->typeRegistry = $typeRegistry;
    }

    public function __invoke()
    {
        $annotationReader = new AnnotationReader($this->container, AnnotationReader::LAX_MODE);
        // $typeGenerator = $this->container->get(TypeGenerator::class);
        // $inputTypeGenerator = $this->container->get(InputTypeGenerator::class);
        // $inputTypeUtils = $this->container->get(InputTypeUtils::class);
        // $namingStrategy = $this->container->get(NamingStrategyInterface::class);

        // $typeMappers[] = new TypeMapper(
        //     'app',
        //     $typeGenerator,
        //     $inputTypeGenerator,
        //     $inputTypeUtils,
        //     $this->container,
        //     $annotationReader,
        //     $namingStrategy,
        //     $this->cache
        // );
        $compositeTypeMapper = new CompositeTypeMapper();
        return new RecursiveTypeMapper($compositeTypeMapper, $this->namingStrategy, $this->cache, $this->typeRegistry, $annotationReader);
    }
}
