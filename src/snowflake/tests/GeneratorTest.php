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

namespace HyperfTest\Snowflake;

use Hyperf\Snowflake\Meta;
use Hyperf\Snowflake\RandomMetaGenerator;
use Hyperf\Snowflake\Snowflake;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GeneratorTest extends TestCase
{
    public function testGenerateReturnInt()
    {
        $generator = new Snowflake(new RandomMetaGenerator());
        $this->assertTrue(is_int($generator->generate()));
    }

    public function testDegenerateInstanceofMeta()
    {
        $generator = new Snowflake(new RandomMetaGenerator());

        $id = $generator->generate();

        $this->assertInstanceOf(Meta::class, $generator->degenerate($id));
    }

    public function testGenerateAndDegenerate()
    {
        $generator = new Snowflake(new RandomMetaGenerator());

        $meta = new Meta(0, 0, 0, 1);

        $id = $generator->generate($meta);

        $this->assertEquals($meta, $generator->degenerate($id)->setTimeInterval(null));
    }

    public function testDegenerateMaxId()
    {
        $generator = new Snowflake(new RandomMetaGenerator());
        $meta = $generator->degenerate(PHP_INT_MAX);
        $days = intval($meta->timeInterval / (3600 * 24));
        $this->assertSame(3181, $days);

        $generator = new Snowflake(new RandomMetaGenerator(), Snowflake::LEVEL_SECOND);
        $meta = $generator->degenerate(PHP_INT_MAX);
        $years = intval($meta->timeInterval / (3600 * 24 * 365));
        $this->assertSame(8716, $years);
    }
}
