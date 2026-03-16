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

namespace HyperfTest\Snowflake;

use Hyperf\Snowflake\Configuration;
use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;
use Hyperf\Snowflake\Meta;
use Hyperf\Snowflake\MetaGenerator\RandomMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SnowflakeGeneratorTest extends TestCase
{
    public function testGenerateReturnInt()
    {
        $config = new Configuration();
        $generator = new SnowflakeIdGenerator(new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND));
        $this->assertTrue(is_int($generator->generate()));
    }

    public function testDegenerateInstanceofMeta()
    {
        $config = new Configuration();
        $generator = new SnowflakeIdGenerator(new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND));

        $id = $generator->generate();

        $this->assertInstanceOf(Meta::class, $generator->degenerate($id));
    }

    public function testGenerateAndDegenerate()
    {
        $config = new Configuration();
        $metaGenerator = new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND);
        $generator = new SnowflakeIdGenerator($metaGenerator);

        $meta = $metaGenerator->generate();
        $id = $generator->generate($meta);
        $this->assertEquals($meta, $generator->degenerate($id));
    }

    public function testDegenerateMaxId()
    {
        $config = new Configuration();
        $metaGenerator = new RandomMilliSecondMetaGenerator($config, MetaGeneratorInterface::DEFAULT_BEGIN_SECOND);
        $generator = new SnowflakeIdGenerator($metaGenerator);

        $meta = $generator->degenerate(PHP_INT_MAX);
        $days = intval($meta->getTimeInterval() / (3600 * 24 * 1000));
        $this->assertSame(25451, $days); // 70 years.
    }
}
