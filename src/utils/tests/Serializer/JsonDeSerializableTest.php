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
namespace HyperfTest\Utils\Serializer;

use Hyperf\Utils\Codec\Json;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonDeSerializableTest extends TestCase
{
    public function testJsonDeSerializable()
    {
        $foo = new Foo(1, 'Hyperf');

        $this->assertSame($json = '{"id":1,"name":"Hyperf"}', Json::encode($foo));

        $foo = Foo::jsonDeSerialize(Json::decode($json));

        $this->assertSame(1, $foo->id);
        $this->assertSame('Hyperf', $foo->name);
    }
}
