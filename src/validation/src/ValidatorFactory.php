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

namespace Hyperf\Validation;

use Hyperf\Contract\TranslatorInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;

class ValidatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $translator = $container->get(TranslatorInterface::class);

        /** @var \Hyperf\Validation\Factory $validatorFactory */
        $validatorFactory = make(Factory::class, compact('translator', 'container'));

        if ($container->has(ConnectionResolverInterface::class) && $container->has(PresenceVerifierInterface::class)) {
            $presenceVerifier = $container->get(PresenceVerifierInterface::class);
            $validatorFactory->setPresenceVerifier($presenceVerifier);
        }

        $validatorFactory->resolver(function (
            TranslatorInterface $translator,
            array $data,
            array $rules,
            array $messages = [],
            array $customAttributes = []
        ) {
            return make(Validator::class, [$translator, $data, $rules, $messages, $customAttributes]);
        });

        return $validatorFactory;
    }
}
