<?php
/**
 * ValidatorFactory.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/7/26 01:31
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation;


use Hyperf\Translation\Contracts\Translator;
use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;

class ValidatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $translator = $container->get(Translator::class);

        $validator = make(Factory::class, compact('translator','container'));

        if ($container->has(ConnectionResolverInterface::class) && $container->has(PresenceVerifierInterface::class)) {
            $presenceVerifier = $container->get(PresenceVerifierInterface::class);
            $validator->setPresenceVerifier($presenceVerifier);
        }

        return $validator;
    }
}