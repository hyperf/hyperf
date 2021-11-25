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
namespace HyperfTest\Utils;

use Hyperf\Utils\Network;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NetworkTest extends TestCase
{
    public function testNetworkIp()
    {
        $this->assertIsString(Network::ip());
    }
}
