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
namespace Hyperf\GraphQL;

use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;

class FieldsBuilderFactory
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var AuthenticationServiceInterface
     */
    private $authenticationService;

    /**
     * @var AuthorizationServiceInterface
     */
    private $authorizationService;

    /**
     * @var CachedDocBlockFactory
     */
    private $cachedDocBlockFactory;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    public function __construct(
        AnnotationReader $annotationReader,
        HydratorInterface $hydrator,
        AuthenticationServiceInterface $authenticationService,
        AuthorizationServiceInterface $authorizationService,
        TypeResolver $typeResolver,
        CachedDocBlockFactory $cachedDocBlockFactory,
        NamingStrategyInterface $namingStrategy
    ) {
        $this->annotationReader = $annotationReader;
        $this->hydrator = $hydrator;
        $this->authenticationService = $authenticationService;
        $this->authorizationService = $authorizationService;
        $this->typeResolver = $typeResolver;
        $this->cachedDocBlockFactory = $cachedDocBlockFactory;
        $this->namingStrategy = $namingStrategy;
    }

    public function buildFieldsBuilder(RecursiveTypeMapperInterface $typeMapper): FieldsBuilder
    {
        return new FieldsBuilder(
            $this->annotationReader,
            $typeMapper,
            new ArgumentResolver($this->hydrator),
            $this->authenticationService,
            $this->authorizationService,
            $this->typeResolver,
            $this->cachedDocBlockFactory,
            $this->namingStrategy
        );
    }
}
