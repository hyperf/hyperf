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

namespace HyperfTest\Amqp;

use HyperfTest\Amqp\Stub\DelayProducerStub;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExchangeBuilderTest extends TestCase
{
    public function testDelayProducer()
    {
        $stub = new DelayProducerStub([]);

        $builder = $stub->getExchangeBuilder();

        $this->assertNotEmpty($builder);
    }
}
