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

namespace HyperfTests\Tappable;

use Hyperf\Tappable\Tappable;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class TappableTest extends TestCase
{
    public function testTappableClassWithCallback()
    {
        $name = TappableClass::make()->tap(function ($tappable) {
            $tappable->setName('MyName');
        })->getName();

        $this->assertSame('MyName', $name);
    }

    public function testTappableClassWithInvokableClass()
    {
        $name = TappableClass::make()->tap(new class {
            public function __invoke($tappable)
            {
                $tappable->setName('MyName');
            }
        })->getName();

        $this->assertSame('MyName', $name);
    }

    public function testTappableClassWithNoneInvokableClass()
    {
        $this->expectException('Error');

        $name = TappableClass::make()->tap(new class {
            public function setName($tappable)
            {
                $tappable->setName('MyName');
            }
        })->getName();

        $this->assertSame('MyName', $name);
    }

    public function testTappableClassWithoutCallback()
    {
        $name = TappableClass::make()->tap()->setName('MyName')->getName();

        $this->assertSame('MyName', $name);
    }
}

class TappableClass
{
    use Tappable;

    private $name;

    public static function make()
    {
        return new static();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
