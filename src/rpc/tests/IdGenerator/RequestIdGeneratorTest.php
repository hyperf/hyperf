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

namespace HyperfTest\Rpc\IdGenerator;

use Hyperf\Rpc\IdGenerator\RequestIdGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RequestIdGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new RequestIdGenerator();
        $id = $generator->generate();
        $this->assertSame(9, strlen($id));
    }
}
