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
use Hyperf\Snowflake\MetaGenerator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MetaGeneratorTest extends TestCase
{
    public function testGenerate()
    {
        $class = new class(new Configuration(), 0) extends MetaGenerator {
            public function getDataCenterId(): int
            {
                return 1;
            }

            public function getWorkerId(): int
            {
                usleep(1000);
                return 1;
            }

            public function getTimestamp(): int
            {
                return 0;
            }

            public function getNextTimestamp(): int
            {
                return 1;
            }
        };

        $callbacks = [];
        for ($i = 0; $i < 10; ++$i) {
            $callbacks[] = static function () use ($class) {
                return $class->generate()->getSequence();
            };
        }

        $res = parallel($callbacks);
        ksort($res);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $res);
    }
}
