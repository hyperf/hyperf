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
namespace Hyperf\Validation;

use Hyperf\Contract\TranslatorInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Validation\Contract\PresenceVerifierInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ValidatorFactoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $translator = $container->get(TranslatorInterface::class);

        /** @var \Hyperf\Validation\ValidatorFactory $validatorFactory */
        $validatorFactory = make(ValidatorFactory::class, compact('translator', 'container'));

        if ($container->has(ConnectionResolverInterface::class) && $container->has(PresenceVerifierInterface::class)) {
            $presenceVerifier = $container->get(PresenceVerifierInterface::class);
            $validatorFactory->setPresenceVerifier($presenceVerifier);
        }

        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new ValidatorFactoryResolved($validatorFactory));

        return $validatorFactory;
    }
}
