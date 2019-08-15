<?php
/**
 * DatabasePresenceVerifierFactory.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019/7/26 01:50
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation;


use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;

class DatabasePresenceVerifierFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $db = $container->get(ConnectionResolverInterface::class);

        $presenceVerifier = make(DatabasePresenceVerifier::class, compact('db'));

        return $presenceVerifier;

    }
}