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
namespace HyperfTest\Utils;

use Exception;
use Hyperf\Support\Exception\ExceptionThrower;
use Hyperf\Support\Filesystem\FileNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExceptionTest extends TestCase
{
    public function testFileNotFoundException()
    {
        try {
            throw new FileNotFoundException();
        } catch (FileNotFoundException) {
            $this->assertTrue(true);
        } catch (\Throwable) {
            $this->assertTrue(false);
        }

        try {
            throw new FileNotFoundException();
        } catch (\Hyperf\Utils\Filesystem\FileNotFoundException) {
            $this->assertTrue(true);
        } catch (\Throwable) {
            $this->assertTrue(false);
        }

        try {
            throw new \Hyperf\Utils\Filesystem\FileNotFoundException();
        } catch (FileNotFoundException) {
            $this->assertTrue(true);
        } catch (\Throwable) {
            $this->assertTrue(false);
        }
    }

    public function testExceptionThrower()
    {
        $thrower = new ExceptionThrower(new Exception('xx'));
        $this->assertTrue($thrower instanceof ExceptionThrower);
        $this->assertTrue($thrower instanceof \Hyperf\Utils\Exception\ExceptionThrower);

        $thrower = new \Hyperf\Utils\Exception\ExceptionThrower(new Exception('xx'));
        $this->assertTrue($thrower instanceof ExceptionThrower);
        $this->assertTrue($thrower instanceof \Hyperf\Utils\Exception\ExceptionThrower);
    }
}
