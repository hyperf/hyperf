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
use Hyperf\View\Exception\EngineNotFindException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NoneTest extends TestCase
{
    public function testRender()
    {
        try {
            $engine = new NoneEngine();
        } catch (\Throwable $throwable) {
            $this->assertInstanceOf(EngineNotFindException::class, $throwable);
            $this->assertSame('No engine available, You can use Blade, Smarty, Twig, Plates and ThinkTemplate.', $throwable->getMessage());
        }
    }
}
