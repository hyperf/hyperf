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

use Hyperf\Database\ConnectionResolverInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class DatabasePresenceVerifierFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $db = $container->get(ConnectionResolverInterface::class);

        return make(DatabasePresenceVerifier::class, compact('db'));
    }
}
