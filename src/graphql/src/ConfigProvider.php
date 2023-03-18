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
use TheCodingMachine\GraphQLite\Hydrators\FactoryHydrator;
use TheCodingMachine\GraphQLite\Hydrators\HydratorInterface;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\NamingStrategy;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;
use TheCodingMachine\GraphQLite\QueryProviderInterface;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Security\FailAuthenticationService;
use TheCodingMachine\GraphQLite\Security\FailAuthorizationService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \GraphQL\Type\Schema::class => \TheCodingMachine\GraphQLite\Schema::class,
                QueryProviderInterface::class => QueryProvider::class,
                RecursiveTypeMapperInterface::class => RecursiveTypeMapperFactory::class,
                Reader::class => ReaderFactory::class,
                HydratorInterface::class => FactoryHydrator::class,
                AuthenticationServiceInterface::class => FailAuthenticationService::class,
                AuthorizationServiceInterface::class => FailAuthorizationService::class,
                NamingStrategyInterface::class => NamingStrategy::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        ClassCollector::class,
                    ],
                ],
            ],
        ];
    }
}
