<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\GraphQL;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\GraphQL\Annotation\Mutation;
use Hyperf\GraphQL\Annotation\Query;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\QueryField;
use TheCodingMachine\GraphQLite\QueryProviderInterface;

class QueryProvider implements QueryProviderInterface
{
    /**
     * @var FieldsBuilderFactory
     */
    private $fieldsBuilderFactory;

    /**
     * @var RecursiveTypeMapperInterface
     */
    private $recursiveTypeMapper;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(FieldsBuilderFactory $fieldsBuilderFactory, RecursiveTypeMapperInterface $recursiveTypeMapper, ContainerInterface $container)
    {
        $this->fieldsBuilderFactory = $fieldsBuilderFactory;
        $this->recursiveTypeMapper = $recursiveTypeMapper;
        $this->container = $container;
    }

    /**
     * @return QueryField[]
     */
    public function getQueries(): array
    {
        $queryList = [];
        $classes = AnnotationCollector::getMethodByAnnotation(Query::class);
        $classes = array_unique(array_column($classes, 'class'));
        foreach ($classes as $className) {
            $fieldsBuilder = $this->fieldsBuilderFactory->buildFieldsBuilder($this->recursiveTypeMapper);
            $queryList = array_merge($queryList, $fieldsBuilder->getQueries($this->container->get($className)));
        }
        return $queryList;
    }

    /**
     * @return QueryField[]
     */
    public function getMutations(): array
    {
        $mutationList = [];
        $classes = AnnotationCollector::getMethodByAnnotation(Mutation::class);
        $classes = array_unique(array_column($classes, 'class'));
        foreach ($classes as $className) {
            $fieldsBuilder = $this->fieldsBuilderFactory->buildFieldsBuilder($this->recursiveTypeMapper);
            $mutationList = array_merge($mutationList, $fieldsBuilder->getMutations($this->container->get($className)));
        }
        return $mutationList;
    }
}
