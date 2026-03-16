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

namespace HyperfTest\Rpc\IdGenerator;

use Hyperf\Rpc\IdGenerator\RequestIdGenerator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RequestIdGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $generator = new RequestIdGenerator();
        $id = $generator->generate();
        $this->assertMatchesRegularExpression('/^\d{2,}$/', $id);
    }
}
