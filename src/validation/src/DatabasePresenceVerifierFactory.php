<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace Hyperf\Validation;

use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;

class DatabasePresenceVerifierFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $db = $container->get(ConnectionResolverInterface::class);

        return make(DatabasePresenceVerifier::class, compact('db'));
    }
}
