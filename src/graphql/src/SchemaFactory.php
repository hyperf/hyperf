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

use PackageVersions\Versions;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\AggregateQueryProvider;
use TheCodingMachine\GraphQLite\FieldsBuilder;
use TheCodingMachine\GraphQLite\GlobControllerQueryProvider;
use TheCodingMachine\GraphQLite\InputTypeGenerator;
use TheCodingMachine\GraphQLite\InputTypeUtils;
use TheCodingMachine\GraphQLite\Mappers\CompositeTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ContainerParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\InjectUserParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ParameterMiddlewarePipe;
use TheCodingMachine\GraphQLite\Mappers\Parameters\PrefetchParameterMiddleware;
use TheCodingMachine\GraphQLite\Mappers\Parameters\ResolveInfoParameterHandler;
use TheCodingMachine\GraphQLite\Mappers\PorpaginasTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\BaseTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\CompoundTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\EnumTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\FinalRootTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\IteratorTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\LastDelegatingTypeMapper;
use TheCodingMachine\GraphQLite\Mappers\Root\NullableTypeMapperAdapter;
use TheCodingMachine\GraphQLite\Mappers\Root\VoidTypeMapper;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\AuthorizationInputFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\CostFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewarePipe;
use TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddleware;
use TheCodingMachine\GraphQLite\Middlewares\SecurityInputFieldMiddleware;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\ParameterizedCallableResolver;
use TheCodingMachine\GraphQLite\Reflection\CachedDocBlockFactory;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\TypeGenerator;
use TheCodingMachine\GraphQLite\TypeRegistry;
use TheCodingMachine\GraphQLite\Types\ArgumentResolver;
use TheCodingMachine\GraphQLite\Types\TypeResolver;
use TheCodingMachine\GraphQLite\Utils\NamespacedCache;
use TheCodingMachine\GraphQLite\Utils\Namespaces\NamespaceFactory;

class SchemaFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $cacheNamespace = substr(md5(Versions::getVersion('thecodingmachine/graphqlite')), 0, 8);
        $cache = $container->get(CacheInterface::class);
        $typeNamespaces = ['App'];
        $controllerNamespaces = ['App'];

        $symfonyCache = new Psr16Adapter($cache, $cacheNamespace);
        $annotationReader = new AnnotationReader($container);
        $authenticationService = new FailAuthenticationService();
        $authorizationService = new FailAuthorizationService();
        $typeResolver = new TypeResolver();
        $namespacedCache = new NamespacedCache($cache);
        $cachedDocBlockFactory = new CachedDocBlockFactory($namespacedCache);
        $namingStrategy = new NamingStrategy();
        $typeRegistry = new TypeRegistry();

        $namespaceFactory = new NamespaceFactory($namespacedCache, null, null);
        $nsList = array_map(
            static fn (string $namespace) => $namespaceFactory->createNamespace($namespace),
            $typeNamespaces,
        );

        $expressionLanguage = new ExpressionLanguage($symfonyCache);
        $expressionLanguage->registerProvider(new SecurityExpressionLanguageProvider());

        $fieldMiddlewarePipe = new FieldMiddlewarePipe();
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityFieldMiddleware)
        $fieldMiddlewarePipe->pipe(new SecurityFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new AuthorizationFieldMiddleware($authenticationService, $authorizationService));
        $fieldMiddlewarePipe->pipe(new CostFieldMiddleware());

        $inputFieldMiddlewarePipe = new InputFieldMiddlewarePipe();
        // TODO: add a logger to the SchemaFactory and make use of it everywhere (and most particularly in SecurityInputFieldMiddleware)
        $inputFieldMiddlewarePipe->pipe(new SecurityInputFieldMiddleware($expressionLanguage, $authenticationService, $authorizationService));
        $inputFieldMiddlewarePipe->pipe(new AuthorizationInputFieldMiddleware($authenticationService, $authorizationService));

        $compositeTypeMapper = new CompositeTypeMapper();
        $recursiveTypeMapper = new RecursiveTypeMapper($compositeTypeMapper, $namingStrategy, $namespacedCache, $typeRegistry, $annotationReader);

        $lastTopRootTypeMapper = new LastDelegatingTypeMapper();
        $topRootTypeMapper = new NullableTypeMapperAdapter($lastTopRootTypeMapper);
        $topRootTypeMapper = new VoidTypeMapper($topRootTypeMapper);

        $errorRootTypeMapper = new FinalRootTypeMapper($recursiveTypeMapper);
        $rootTypeMapper = new BaseTypeMapper($errorRootTypeMapper, $recursiveTypeMapper, $topRootTypeMapper);
        $rootTypeMapper = new EnumTypeMapper($rootTypeMapper, $annotationReader, $symfonyCache, $nsList);

        $rootTypeMapper = new CompoundTypeMapper($rootTypeMapper, $topRootTypeMapper, $namingStrategy, $typeRegistry, $recursiveTypeMapper);
        $rootTypeMapper = new IteratorTypeMapper($rootTypeMapper, $topRootTypeMapper);

        $lastTopRootTypeMapper->setNext($rootTypeMapper);

        $argumentResolver = new ArgumentResolver();
        $parameterMiddlewarePipe = new ParameterMiddlewarePipe();

        $fieldsBuilder = new FieldsBuilder(
            $annotationReader,
            $recursiveTypeMapper,
            $argumentResolver,
            $typeResolver,
            $cachedDocBlockFactory,
            $namingStrategy,
            $topRootTypeMapper,
            $parameterMiddlewarePipe,
            $fieldMiddlewarePipe,
            $inputFieldMiddlewarePipe,
        );
        $parameterizedCallableResolver = new ParameterizedCallableResolver($fieldsBuilder, $container);

        $parameterMiddlewarePipe->pipe(new ResolveInfoParameterHandler());
        $parameterMiddlewarePipe->pipe(new PrefetchParameterMiddleware($parameterizedCallableResolver));
        $parameterMiddlewarePipe->pipe(new ContainerParameterHandler($container));
        $parameterMiddlewarePipe->pipe(new InjectUserParameterHandler($authenticationService));

        $typeGenerator = new TypeGenerator($annotationReader, $namingStrategy, $typeRegistry, $container, $recursiveTypeMapper, $fieldsBuilder);
        $inputTypeUtils = new InputTypeUtils($annotationReader, $namingStrategy);
        $inputTypeGenerator = new InputTypeGenerator($inputTypeUtils, $fieldsBuilder);

        foreach ($nsList as $ns) {
            $compositeTypeMapper->addTypeMapper(new GlobTypeMapper(
                $ns,
                $typeGenerator,
                $inputTypeGenerator,
                $inputTypeUtils,
                $container,
                $annotationReader,
                $namingStrategy,
                $recursiveTypeMapper,
                $namespacedCache,
            ));
        }

        $compositeTypeMapper->addTypeMapper(new PorpaginasTypeMapper($recursiveTypeMapper));

        $queryProviders = [];
        foreach ($controllerNamespaces as $controllerNamespace) {
            $queryProviders[] = new GlobControllerQueryProvider(
                $controllerNamespace,
                $fieldsBuilder,
                $container,
                $annotationReader,
                $namespacedCache,
            );
        }

        $aggregateQueryProvider = new AggregateQueryProvider($queryProviders);

        return new Schema($aggregateQueryProvider, $recursiveTypeMapper, $typeResolver, $topRootTypeMapper);
    }
}
