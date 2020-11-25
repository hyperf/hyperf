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
namespace Hyperf\Encryption;

use Psr\Container\ContainerInterface;

class EncrypterInvoker
{
    public function __invoke(ContainerInterface $container)
    {
        $factory = $container->get(EncrypterFactory::class);

        return $factory->get('default');
    }
}
