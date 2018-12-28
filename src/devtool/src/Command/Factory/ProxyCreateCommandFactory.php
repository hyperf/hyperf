<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Devtool\Command\Factory;

use Hyperf\Devtool\Command\ProxyCreateCommand;
use Hyperf\Di\Annotation\Scanner;
use Psr\Container\ContainerInterface;

class ProxyCreateCommandFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ProxyCreateCommand($container, $container->get(Scanner::class));
    }
}
