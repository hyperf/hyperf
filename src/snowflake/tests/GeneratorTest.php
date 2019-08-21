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

use Hyperf\Snowflake\Config;
use Hyperf\Snowflake\IdGenerator\MilliSecondIdGenerator;
use Hyperf\Snowflake\Meta;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;
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
        $config = new Config();
        $generator = new MilliSecondIdGenerator(new RandomMilliSecondMetaGenerator($config), $config);
        $this->assertTrue(is_int($generator->generate()));
    }

    public function testDegenerateInstanceofMeta()
    {
        $config = new Config();
        $generator = new MilliSecondIdGenerator(new RandomMilliSecondMetaGenerator($config), $config);

        $id = $generator->generate();

        $this->assertInstanceOf(Meta::class, $generator->degenerate($id));
    }

    public function testGenerateAndDegenerate()
    {
        $config = new Config();
        $metaGenerator = new RandomMilliSecondMetaGenerator($config);
        $generator = new MilliSecondIdGenerator($metaGenerator, $config);

        $meta = $metaGenerator->generate();
        $id = $generator->generate($meta);
        $this->assertEquals($meta, $generator->degenerate($id));
    }

    public function testDegenerateMaxId()
    {
        $config = new Config();
        $metaGenerator = new RandomMilliSecondMetaGenerator($config);
        $generator = new MilliSecondIdGenerator($metaGenerator, $config);

        $meta = $generator->degenerate(PHP_INT_MAX);
        $days = intval(($meta->timestamp) / (3600 * 24 * 1000));
        $this->assertSame(43573, $days);

        // $generator = new Snowflake(new RandomMetaGenerator(), Snowflake::LEVEL_SECOND, 0);
        // $meta = $generator->degenerate(PHP_INT_MAX);
        // $years = intval($meta->timestamp / (3600 * 24 * 365));
        // $this->assertSame(8716, $years);
    }
}
