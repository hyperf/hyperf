<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Rpc\PathGenerator;

use Hyperf\Rpc\PathGenerator\FullPathGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FullPathGeneratorTest extends TestCase
{
    public function testGeneratorFromClassName()
    {
        $pathGenerator = new FullPathGenerator();
        $this->assertEquals('/Foo/UserService/query', $pathGenerator->generate('Foo\\UserService', 'query'));
    }

    public function testGeneratorFromName()
    {
        $pathGenerator = new FullPathGenerator();
        $this->assertEquals('/user/query', $pathGenerator->generate('user', 'query'));
    }
}
