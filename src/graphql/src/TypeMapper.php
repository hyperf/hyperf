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

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\OutputType;
use Hyperf\GraphQL\Annotation\Type;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Lock\Factory as LockFactory;
use Symfony\Component\Lock\Lock;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeException;
use TheCodingMachine\GraphQLite\Mappers\CannotMapTypeExceptionInterface;
use TheCodingMachine\GraphQLite\Mappers\DuplicateMappingException;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

use function array_keys;
use function class_exists;
use function filemtime;

/**
 * Scans all the classes in a given namespace of the main project (not the vendor directory).
 * Analyzes all classes and uses the Type annotation to find the types automatically.
 *
 * Assumes that the container contains a class whose identifier is the same as the class name.
 */
class TypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var null|int
     */
    private $globTtl;

    /**
     * @var array<string,string> Maps a domain class to the GraphQL type annotated class
     */
    private $mapClassToTypeArray = [];

    /**
     * @var array<string,array<string,string>> Maps a domain class to one or many type extenders (with the ExtendType annotation) The array of type extenders has a key and value equals to FQCN
     */
    private $mapClassToExtendTypeArray = [];

    /**
     * @var array<string,string> Maps a GraphQL type name to the GraphQL type annotated class
     */
    private $mapNameToType = [];

    /**
     * @var array<string,array<string,string>> Maps a GraphQL type name to one or many type extenders (with the ExtendType annotation) The array of type extenders has a key and value equals to FQCN
     */
    private $mapNameToExtendType = [];

    /**
     * @var array<string,string[]> Maps a domain class to the factory method that creates the input type in the form [classname, methodname]
     */
    private $mapClassToFactory = [];

    /**
     * @var array<string,string[]> Maps a GraphQL input type name to the factory method that creates the input type in the form [classname, methodname]
     */
    private $mapInputNameToFactory = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TypeGenerator
     */
    private $typeGenerator;

    /**
     * @var null|int
     */
    private $mapTtl;

    /**
     * @var bool
     */
    private $fullMapComputed = false;

    /**
     * @var bool
     */
    private $fullMapClassToExtendTypeArrayComputed = false;

    /**
     * @var bool
     */
    private $fullMapNameToExtendTypeArrayComputed = false;

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var InputTypeGenerator
     */
    private $inputTypeGenerator;

    /**
     * @var InputTypeUtils
     */
    private $inputTypeUtils;

    /**
     * The array of globbed classes.
     * Only instantiable classes are returned.
     * Key: fully qualified class name.
     *
     * @var array<string,ReflectionClass>
     */
    private $classes;

    /**
     * @var bool
     */
    private $recursive;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * @param string $namespace The namespace that contains the GraphQL types (they must have a `@Type` annotation)
     */
    public function __construct(string $namespace, TypeGenerator $typeGenerator, InputTypeGenerator $inputTypeGenerator, InputTypeUtils $inputTypeUtils, ContainerInterface $container, AnnotationReader $annotationReader, NamingStrategyInterface $namingStrategy, LockFactory $lockFactory, CacheInterface $cache, ?int $globTtl = 2, ?int $mapTtl = null, bool $recursive = true)
    {
        $this->namespace = $namespace;
        $this->typeGenerator = $typeGenerator;
        $this->container = $container;
        $this->annotationReader = $annotationReader;
        $this->namingStrategy = $namingStrategy;
        $this->cache = $cache;
        $this->globTtl = $globTtl;
        $this->mapTtl = $mapTtl;
        $this->inputTypeGenerator = $inputTypeGenerator;
        $this->inputTypeUtils = $inputTypeUtils;
        $this->recursive = $recursive;
        $this->lockFactory = $lockFactory;
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL type.
     */
    public function canMapClassToType(string $className): bool
    {
        $typeClassName = $this->getTypeFromCacheByObjectClass($className);

        if ($typeClassName !== null) {
            return true;
        }

        $map = $this->getMapClassToType();

        return isset($map[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL type.
     *
     * @param string $className the exact class name to look for (this function does not look into parent classes)
     * @param null|OutputType $subType an optional sub-type if the main class is an iterator that needs to be typed
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToType(string $className, ?OutputType $subType, RecursiveTypeMapperInterface $recursiveTypeMapper): MutableObjectType
    {
        $typeClassName = $this->getTypeFromCacheByObjectClass($className);

        if ($typeClassName === null) {
            $map = $this->getMapClassToType();
            if (! isset($map[$className])) {
                throw CannotMapTypeException::createForType($className);
            }
            $typeClassName = $map[$className];
        }

        return $this->typeGenerator->mapAnnotatedObject($typeClassName, $recursiveTypeMapper);
    }

    /**
     * Returns the list of classes that have matching input GraphQL types.
     *
     * @return string[]
     */
    public function getSupportedClasses(): array
    {
        return array_keys($this->getMapClassToType());
    }

    /**
     * Returns true if this type mapper can map the $className FQCN to a GraphQL input type.
     */
    public function canMapClassToInputType(string $className): bool
    {
        $factory = $this->getFactoryFromCacheByObjectClass($className);

        if ($factory !== null) {
            return true;
        }
        $map = $this->getMapClassToFactory();
        return isset($map[$className]);
    }

    /**
     * Maps a PHP fully qualified class name to a GraphQL input type.
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function mapClassToInputType(string $className, RecursiveTypeMapperInterface $recursiveTypeMapper): InputObjectType
    {
        $factory = $this->getFactoryFromCacheByObjectClass($className);

        if ($factory === null) {
            $map = $this->getMapClassToFactory();
            if (! isset($map[$className])) {
                throw CannotMapTypeException::createForInputType($className);
            }
            $factory = $map[$className];
        }

        return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $recursiveTypeMapper, $this->container);
    }

    /**
     * Returns a GraphQL type by name (can be either an input or output type).
     *
     * @param string $typeName The name of the GraphQL type
     * @return \GraphQL\Type\Definition\Type&(InputType|OutputType)
     * @throws CannotMapTypeExceptionInterface
     * @throws ReflectionException
     */
    public function mapNameToType(string $typeName, RecursiveTypeMapperInterface $recursiveTypeMapper): \GraphQL\Type\Definition\Type
    {
        $typeClassName = $this->getTypeFromCacheByGraphQLTypeName($typeName);
        if ($typeClassName === null) {
            $factory = $this->getFactoryFromCacheByGraphQLInputTypeName($typeName);
            if ($factory === null) {
                $mapNameToType = $this->getMapNameToType();
                if (isset($mapNameToType[$typeName])) {
                    $typeClassName = $mapNameToType[$typeName];
                } else {
                    $mapInputNameToFactory = $this->getMapInputNameToFactory();
                    if (isset($mapInputNameToFactory[$typeName])) {
                        $factory = $mapInputNameToFactory[$typeName];
                    }
                }
            }
        }

        if (isset($typeClassName)) {
            return $this->typeGenerator->mapAnnotatedObject($typeClassName, $recursiveTypeMapper);
        }
        if (isset($factory)) {
            return $this->inputTypeGenerator->mapFactoryMethod($factory[0], $factory[1], $recursiveTypeMapper, $this->container);
        }

        throw CannotMapTypeException::createForName($typeName);
    }

    /**
     * Returns true if this type mapper can map the $typeName GraphQL name to a GraphQL type.
     *
     * @param string $typeName The name of the GraphQL type
     */
    public function canMapNameToType(string $typeName): bool
    {
        $typeClassName = $this->getTypeFromCacheByGraphQLTypeName($typeName);

        if ($typeClassName !== null) {
            return true;
        }

        $factory = $this->getFactoryFromCacheByGraphQLInputTypeName($typeName);
        if ($factory !== null) {
            return true;
        }

        $this->getMaps();

        return isset($this->mapNameToType[$typeName]) || isset($this->mapInputNameToFactory[$typeName]);
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $className FQCN.
     */
    public function canExtendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
    {
        $extendTypeClassName = $this->getExtendTypesFromCacheByObjectClass($className);

        if ($extendTypeClassName === null) {
            $map = $this->getMapClassToExtendTypeArray();
        }

        return isset($this->mapClassToExtendTypeArray[$className]);
    }

    /**
     * Extends the existing GraphQL type that is mapped to $className.
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForClass(string $className, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        $extendTypeClassNames = $this->getExtendTypesFromCacheByObjectClass($className);

        if ($extendTypeClassNames === null) {
            $this->getMapClassToExtendTypeArray();
        }

        if (! isset($this->mapClassToExtendTypeArray[$className])) {
            throw CannotMapTypeException::createForExtendType($className, $type);
        }

        foreach ($this->mapClassToExtendTypeArray[$className] as $extendedTypeClass) {
            $this->typeGenerator->extendAnnotatedObject($this->container->get($extendedTypeClass), $type, $recursiveTypeMapper);
        }
    }

    /**
     * Returns true if this type mapper can extend an existing type for the $typeName GraphQL type.
     */
    public function canExtendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): bool
    {
        $typeClassNames = $this->getExtendTypesFromCacheByGraphQLTypeName($typeName);

        if ($typeClassNames !== null) {
            return true;
        }

        /*$factory = $this->getFactoryFromCacheByGraphQLInputTypeName($typeName);
        if ($factory !== null) {
            return true;
        }*/

        $map = $this->getMapNameToExtendType($recursiveTypeMapper);

        return isset($map[$typeName])/* || isset($this->mapInputNameToFactory[$typeName]) */;
    }

    /**
     * Extends the existing GraphQL type that is mapped to the $typeName GraphQL type.
     *
     * @throws CannotMapTypeExceptionInterface
     */
    public function extendTypeForName(string $typeName, MutableObjectType $type, RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        $extendTypeClassNames = $this->getExtendTypesFromCacheByGraphQLTypeName($typeName);
        if ($extendTypeClassNames === null) {
            /*$factory = $this->getFactoryFromCacheByGraphQLInputTypeName($typeName);
            if ($factory === null) {*/
            $map = $this->getMapNameToExtendType($recursiveTypeMapper);
            if (! isset($map[$typeName])) {
                throw CannotMapTypeException::createForExtendName($typeName, $type);
            }
            $extendTypeClassNames = $map[$typeName];

            // }
        }

        foreach ($extendTypeClassNames as $extendedTypeClass) {
            $this->typeGenerator->extendAnnotatedObject($this->container->get($extendedTypeClass), $type, $recursiveTypeMapper);
        }

        /*if (isset($this->mapInputNameToFactory[$typeName])) {
            $factory = $this->mapInputNameToFactory[$typeName];
            return $this->inputTypeGenerator->mapFactoryMethod($this->container->get($factory[0]), $factory[1], $recursiveTypeMapper);
        }*/
    }

    /**
     * Returns an array of fully qualified class names.
     *
     * @return array<string, array<string,string>>
     */
    private function getMaps(): array
    {
        if ($this->fullMapComputed === false) {
            $namespace = str_replace('\\', '_', $this->namespace);
            $keyClassCache = 'globTypeMapper_' . $namespace;
            $keyNameCache = 'globTypeMapper_names_' . $namespace;
            $keyInputClassCache = 'globInputTypeMapper_' . $namespace;
            $keyInputNameCache = 'globInputTypeMapper_names_' . $namespace;
            $this->mapClassToTypeArray = $this->cache->get($keyClassCache);
            $this->mapNameToType = $this->cache->get($keyNameCache);
            $this->mapClassToFactory = $this->cache->get($keyInputClassCache);
            $this->mapInputNameToFactory = $this->cache->get($keyInputNameCache);
            if ($this->mapClassToTypeArray === null
                || $this->mapNameToType === null
                || $this->mapClassToFactory === null
                || $this->mapInputNameToFactory === null
            ) {
                $lock = $this->lockFactory->createLock('buildmap_' . $this->namespace, 5);
                if (! $lock->acquire()) {
                    // Lock is being held right now. Generation is happening.
                    // Let's wait and fetch the result from the cache.
                    $lock->acquire(true);
                    $lock->release();
                    return $this->getMaps();
                }
                try {
                    $this->buildMap();
                } finally {
                    $lock->release();
                }
                // This is a very short lived cache. Useful to avoid overloading a server in case of heavy load.
                // Defaults to 2 seconds.
                $this->cache->set($keyClassCache, $this->mapClassToTypeArray, $this->globTtl);
                $this->cache->set($keyNameCache, $this->mapNameToType, $this->globTtl);
                $this->cache->set($keyInputClassCache, $this->mapClassToFactory, $this->globTtl);
                $this->cache->set($keyInputNameCache, $this->mapInputNameToFactory, $this->globTtl);
            }
            $this->fullMapComputed = true;
        }
        return [
            'mapClassToTypeArray' => $this->mapClassToTypeArray,
            'mapNameToType' => $this->mapNameToType,
            'mapClassToFactory' => $this->mapClassToFactory,
            'mapInputNameToFactory' => $this->mapInputNameToFactory,
        ];
    }

    private function getMapClassToType(): array
    {
        return $this->getMaps()['mapClassToTypeArray'];
    }

    private function getMapNameToType(): array
    {
        return $this->getMaps()['mapNameToType'];
    }

    private function getMapClassToFactory(): array
    {
        return $this->getMaps()['mapClassToFactory'];
    }

    private function getMapInputNameToFactory(): array
    {
        return $this->getMaps()['mapInputNameToFactory'];
    }

    private function getMapClassToExtendTypeArray(): array
    {
        if ($this->fullMapClassToExtendTypeArrayComputed === false) {
            $namespace = str_replace('\\', '_', $this->namespace);
            $keyExtendClassCache = 'globTypeMapperExtend_' . $namespace;
            $this->mapClassToExtendTypeArray = $this->cache->get($keyExtendClassCache);
            if ($this->mapClassToExtendTypeArray === null) {
                $lock = $this->lockFactory->createLock('buildmapclassextend_' . $this->namespace, 5);
                if (! $lock->acquire()) {
                    // Lock is being held right now. Generation is happening.
                    // Let's wait and fetch the result from the cache.
                    $lock->acquire(true);
                    $lock->release();
                    return $this->getMapClassToExtendTypeArray();
                }
                $lock->acquire(true);
                try {
                    $this->buildMapClassToExtendTypeArray($lock);
                } finally {
                    $lock->release();
                }
                // This is a very short lived cache. Useful to avoid overloading a server in case of heavy load.
                // Defaults to 2 seconds.
                $this->cache->set($keyExtendClassCache, $this->mapClassToExtendTypeArray, $this->globTtl);
            }
            $this->fullMapClassToExtendTypeArrayComputed = true;
        }
        return $this->mapClassToExtendTypeArray;
    }

    private function getMapNameToExtendType(RecursiveTypeMapperInterface $recursiveTypeMapper): array
    {
        if ($this->fullMapNameToExtendTypeArrayComputed === false) {
            $namespace = str_replace('\\', '_', $this->namespace);
            $keyExtendNameCache = 'globTypeMapperExtend_names_' . $namespace;
            $this->mapNameToExtendType = $this->cache->get($keyExtendNameCache);
            if ($this->mapNameToExtendType === null) {
                $lock = $this->lockFactory->createLock('buildmapnameextend_' . $this->namespace, 5);
                if (! $lock->acquire()) {
                    // Lock is being held right now. Generation is happening.
                    // Let's wait and fetch the result from the cache.
                    $lock->acquire(true);
                    $lock->release();
                    return $this->getMapNameToExtendType($recursiveTypeMapper);
                }
                $lock->acquire(true);
                try {
                    $this->buildMapNameToExtendTypeArray($recursiveTypeMapper);
                } finally {
                    $lock->release();
                }
                // This is a very short lived cache. Useful to avoid overloading a server in case of heavy load.
                // Defaults to 2 seconds.
                $this->cache->set($keyExtendNameCache, $this->mapNameToExtendType, $this->globTtl);
            }
            $this->fullMapNameToExtendTypeArrayComputed = true;
        }
        return $this->mapNameToExtendType;
    }

    /**
     * Returns the array of globbed classes.
     * Only instantiable classes are returned.
     *
     * @return array<string,ReflectionClass> Key: fully qualified class name
     */
    private function getClassList(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            $classes = ClassCollector::getClasses();
            foreach ($classes as $className) {
                if (! class_exists($className)) {
                    continue;
                }
                $refClass = new ReflectionClass($className);
                if (! $refClass->isInstantiable()) {
                    continue;
                }
                $this->classes[$className] = $refClass;
            }
        }
        return $this->classes;
    }

    private function buildMap(): void
    {
        $this->mapClassToTypeArray = [];
        $this->mapNameToType = [];
        $this->mapClassToFactory = [];
        $this->mapInputNameToFactory = [];

        /** @var ReflectionClass[] $classes */
        $classes = $this->getClassList();
        foreach ($classes as $className => $refClass) {
            $type = $this->annotationReader->getTypeAnnotation($refClass);

            if ($type !== null) {
                if (isset($this->mapClassToTypeArray[$type->getClass()])) {
                    throw DuplicateMappingException::createForType($type->getClass(), $this->mapClassToTypeArray[$type->getClass()], $className);
                }
                $this->storeTypeInCache($className, $type, $refClass->getFileName());
            }

            $isAbstract = $refClass->isAbstract();

            foreach ($refClass->getMethods() as $method) {
                if (! $method->isPublic() || ($isAbstract && ! $method->isStatic())) {
                    continue;
                }
                $factory = $this->annotationReader->getFactoryAnnotation($method);
                if ($factory !== null) {
                    [$inputName, $className] = $this->inputTypeUtils->getInputTypeNameAndClassName($method);

                    if (isset($this->mapClassToFactory[$className])) {
                        throw DuplicateMappingException::createForFactory($className, $this->mapClassToFactory[$className][0], $this->mapClassToFactory[$className][1], $refClass->getName(), $method->name);
                    }
                    $this->storeInputTypeInCache($method, $inputName, $className, $refClass->getFileName());
                }
            }
        }
    }

    private function buildMapClassToExtendTypeArray(Lock $lock): void
    {
        $lock->acquire(true);
        try {
            $this->mapClassToExtendTypeArray = [];
            $classes = $this->getClassList();
            foreach ($classes as $className => $refClass) {
                $extendType = $this->annotationReader->getExtendTypeAnnotation($refClass);

                if ($extendType !== null) {
                    $this->storeExtendTypeMapperByClassInCache($className, $extendType, $refClass->getFileName());
                }
            }
        } finally {
            $lock->release();
        }
    }

    private function buildMapNameToExtendTypeArray(RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        $this->mapNameToExtendType = [];
        $classes = $this->getClassList();
        foreach ($classes as $className => $refClass) {
            $extendType = $this->annotationReader->getExtendTypeAnnotation($refClass);

            if ($extendType !== null) {
                $this->storeExtendTypeMapperByNameInCache($className, $extendType, $refClass->getFileName(), $recursiveTypeMapper);
            }
        }
    }

    /**
     * Stores in cache the mapping TypeClass <=> Object class <=> GraphQL type name.
     */
    private function storeTypeInCache(string $typeClassName, Type $type, string $typeFileName): void
    {
        $objectClassName = $type->getClass();
        $this->mapClassToTypeArray[$objectClassName] = $typeClassName;
        $this->cache->set('globTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $objectClassName), [
            'filemtime' => filemtime($typeFileName),
            'fileName' => $typeFileName,
            'typeClass' => $typeClassName,
        ], $this->mapTtl);
        $typeName = $this->namingStrategy->getOutputTypeName($typeClassName, $type);
        $this->mapNameToType[$typeName] = $typeClassName;
        $this->cache->set('globTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $typeName, [
            'filemtime' => filemtime($typeFileName),
            'fileName' => $typeFileName,
            'typeClass' => $typeClassName,
        ], $this->mapTtl);
    }

    /**
     * Stores in cache the mapping between InputType name <=> Object class.
     */
    private function storeInputTypeInCache(ReflectionMethod $refMethod, string $inputName, string $className, string $fileName): void
    {
        $refArray = [$refMethod->getDeclaringClass()->getName(), $refMethod->getName()];
        $this->mapClassToFactory[$className] = $refArray;
        $this->cache->set('globInputTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $className), [
            'filemtime' => filemtime($fileName),
            'fileName' => $fileName,
            'factory' => $refArray,
        ], $this->mapTtl);
        $this->mapInputNameToFactory[$inputName] = $refArray;
        $this->cache->set('globInputTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $inputName, [
            'filemtime' => filemtime($fileName),
            'fileName' => $fileName,
            'factory' => $refArray,
        ], $this->mapTtl);
    }

    /**
     * Stores in cache the mapping ExtendTypeClass <=> Object class.
     */
    private function storeExtendTypeMapperByClassInCache(string $extendTypeClassName, ExtendType $extendType, string $typeFileName): void
    {
        $objectClassName = $extendType->getClass();
        $this->mapClassToExtendTypeArray[$objectClassName][$extendTypeClassName] = $extendTypeClassName;
        $this->cache->set('globExtendTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $objectClassName), [
            'filemtime' => filemtime($typeFileName),
            'fileName' => $typeFileName,
            'extendTypeClasses' => $this->mapClassToExtendTypeArray[$objectClassName],
        ], $this->mapTtl);
    }

    /**
     * Stores in cache the mapping ExtendTypeClass <=> name class.
     */
    private function storeExtendTypeMapperByNameInCache(string $extendTypeClassName, ExtendType $extendType, string $typeFileName, RecursiveTypeMapperInterface $recursiveTypeMapper): void
    {
        $targetType = $recursiveTypeMapper->mapClassToType($extendType->getClass(), null);
        $typeName = $targetType->name;

        $this->mapNameToExtendType[$typeName][$extendTypeClassName] = $extendTypeClassName;
        $this->cache->set('globExtendTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $typeName, [
            'filemtime' => filemtime($typeFileName),
            'fileName' => $typeFileName,
            'extendTypeClasses' => $this->mapNameToExtendType[$typeName],
        ], $this->mapTtl);
    }

    private function getTypeFromCacheByObjectClass(string $className): ?string
    {
        if (isset($this->mapClassToTypeArray[$className])) {
            return $this->mapClassToTypeArray[$className];
        }

        // Let's try from the cache
        $item = $this->cache->get('globTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $className));
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'typeClass' => $typeClassName
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapClassToTypeArray[$className] = $typeClassName;
                return $typeClassName;
            }
        }

        // cache miss
        return null;
    }

    private function getTypeFromCacheByGraphQLTypeName(string $graphqlTypeName): ?string
    {
        if (isset($this->mapNameToType[$graphqlTypeName])) {
            return $this->mapNameToType[$graphqlTypeName];
        }

        // Let's try from the cache
        $item = $this->cache->get('globTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $graphqlTypeName);
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'typeClass' => $typeClassName
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapNameToType[$graphqlTypeName] = $typeClassName;
                return $typeClassName;
            }
        }

        // cache miss
        return null;
    }

    /**
     * @return null|string[] A pointer to the factory [$className, $methodName] or null on cache miss
     */
    private function getFactoryFromCacheByObjectClass(string $className): ?array
    {
        if (isset($this->mapClassToFactory[$className])) {
            return $this->mapClassToFactory[$className];
        }

        // Let's try from the cache
        $item = $this->cache->get('globInputTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $className));
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'factory' => $factory
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapClassToFactory[$className] = $factory;
                return $factory;
            }
        }

        // cache miss
        return null;
    }

    /**
     * @return null|array<string,string> An array of classes with the ExtendType annotation (key and value = FQCN)
     */
    private function getExtendTypesFromCacheByObjectClass(string $className): ?array
    {
        if (isset($this->mapClassToExtendTypeArray[$className])) {
            return $this->mapClassToExtendTypeArray[$className];
        }

        // Let's try from the cache
        $item = $this->cache->get('globExtendTypeMapperByClass_' . str_replace('\\', '_', $this->namespace) . '_' . str_replace('\\', '_', $className));
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'extendTypeClasses' => $extendTypeClassNames
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapClassToExtendTypeArray[$className] = $extendTypeClassNames;
                return $extendTypeClassNames;
            }
        }

        // cache miss
        return null;
    }

    /**
     * @return null|array<string,string> An array of classes with the ExtendType annotation (key and value = FQCN)
     */
    private function getExtendTypesFromCacheByGraphQLTypeName(string $graphqlTypeName): ?array
    {
        if (isset($this->mapNameToExtendType[$graphqlTypeName])) {
            return $this->mapNameToExtendType[$graphqlTypeName];
        }

        // Let's try from the cache
        $item = $this->cache->get('globExtendTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $graphqlTypeName);
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'extendTypeClasses' => $extendTypeClassNames
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapNameToExtendType[$graphqlTypeName] = $extendTypeClassNames;
                return $extendTypeClassNames;
            }
        }

        // cache miss
        return null;
    }

    /**
     * @return null|string[] A pointer to the factory [$className, $methodName] or null on cache miss
     */
    private function getFactoryFromCacheByGraphQLInputTypeName(string $graphqlTypeName): ?array
    {
        if (isset($this->mapInputNameToFactory[$graphqlTypeName])) {
            return $this->mapInputNameToFactory[$graphqlTypeName];
        }

        // Let's try from the cache
        $item = $this->cache->get('globInputTypeMapperByName_' . str_replace('\\', '_', $this->namespace) . '_' . $graphqlTypeName);
        if ($item !== null) {
            [
                'filemtime' => $filemtime,
                'fileName' => $typeFileName,
                'factory' => $factory
            ] = $item;

            if ($filemtime === @filemtime($typeFileName)) {
                $this->mapInputNameToFactory[$graphqlTypeName] = $factory;
                return $factory;
            }
        }

        // cache miss
        return null;
    }
}
