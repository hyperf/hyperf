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
namespace Hyperf\Testing;

use Mockery as m;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use Concerns\InteractsWithContainer;
    use Concerns\MakesHttpRequests;

    protected function setUp(): void
    {
        /* @phpstan-ignore-next-line */
        if (! $this->container) {
            $this->refreshContainer();
        }
    }

    protected function tearDown(): void
    {
        $this->container = null;

        try {
            m::close();
        } catch (Throwable) {
        }
    }
}
