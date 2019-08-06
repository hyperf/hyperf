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

namespace HyperfTest\Config;

use Hyperf\Config\ProviderConfig;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProviderConfigTest extends TestCase
{
    public function testProviderConfigMerge()
    {
        $c1 = [
            'listeners' => ['L1'],
            'dependencies' => [
                'D1' => 'D1',
                'D2' => 'D2',
            ],
        ];

        $c2 = [
            'listeners' => ['L2'],
            'dependencies' => [
                'D1' => 'D1',
                'D2' => 'D3',
            ],
        ];

        $c3 = [
            'listeners' => ['L2'],
            'dependencies' => [
                'D1' => 'D1',
                'D3' => 'D3',
                'D4' => 'D4',
            ],
        ];

        $result = ProviderConfig::merge($c1, $c2, $c3);

        $this->assertSame(['D1' => 'D1', 'D2' => 'D3', 'D3' => 'D3', 'D4' => 'D4'], $result['dependencies']);
    }
}
