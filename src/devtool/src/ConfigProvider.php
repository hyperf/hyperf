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

namespace Hyperf\Devtool;

use Hyperf\Devtool\Command\Factory\ProxyCreateCommandFactory;
use Hyperf\Devtool\Command\ProxyCreateCommand;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
