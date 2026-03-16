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

namespace HyperfTest\View;

use Hyperf\View\Engine\NoneEngine;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class NoneTest extends TestCase
{
    public function testRender()
    {
        $content = (new NoneEngine())->render('/', [], []);

        $this->assertNotEmpty($content);
    }
}
