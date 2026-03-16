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

namespace HyperfTest\ServiceGovernance;

use Hyperf\ServiceGovernance\ServiceManager;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ServiceManagerTest extends TestCase
{
    public function testRegister()
    {
        $manager = new ServiceManager();
        $manager->register('demo', 'index/demo', [
            'protocol' => 'jsonrpc',
        ]);
        $manager->register('demo', 'index/demo', [
            'protocol' => 'jsonrpc-http',
        ]);

        $this->assertEquals([
            'demo' => [
                'index/demo' => [
                    'jsonrpc' => ['protocol' => 'jsonrpc'],
                    'jsonrpc-http' => ['protocol' => 'jsonrpc-http'],
                ],
            ],
        ], $manager->all());
    }
}
