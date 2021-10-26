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
namespace HyperfTest\Kafka;

/**
 * @internal
 * @coversNothing
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $this->markTestSkipped('Kafka Client only support php 7.4');
        }
    }
}
