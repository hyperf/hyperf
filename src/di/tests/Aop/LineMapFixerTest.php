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

namespace HyperfTest\Di\Aop;

use Hyperf\Di\Aop\LineMapFixer;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class LineMapFixerTest extends TestCase
{
    public function testTranslateDoesNotExceedTheOriginalRange(): void
    {
        $translate = new ReflectionMethod(LineMapFixer::class, 'translate');
        $map = [
            'ranges' => [[100, 110, 20, 22]],
        ];

        $this->assertSame(20, $translate->invoke(null, $map, 100));
        $this->assertSame(22, $translate->invoke(null, $map, 105));
        $this->assertNull($translate->invoke(null, $map, 99));
    }

    public function testFormatResolvesProxyLocationWithoutModifyingThrowable(): void
    {
        $directory = sys_get_temp_dir() . '/hyperf-line-map-' . uniqid();
        mkdir($directory);
        $proxyFile = $directory . '/App_LineMapStub.proxy.php';
        $originFile = $directory . '/LineMapStub.php';
        file_put_contents(
            $proxyFile,
            "<?php\n\n\$throw = static function (): void { throw new \\RuntimeException('line map'); };"
            . str_repeat("\n", 27)
            . "\$throw();\n"
        );
        file_put_contents($directory . '/line-map.php', sprintf(
            "<?php\nreturn ['maps' => ['App\\\\LineMapStub' => ['file' => %s, 'ranges' => [[3, 3, 42, 42], [30, 30, 60, 60]], 'methods' => []]]];\n",
            var_export($originFile, true)
        ));

        try {
            require $proxyFile;
            $this->fail('The proxy fixture must throw an exception.');
        } catch (RuntimeException $exception) {
            $formatted = LineMapFixer::format($exception);

            $this->assertStringContainsString($originFile . ':42', $formatted);
            $this->assertStringContainsString($originFile . '(60)', $formatted);
            $this->assertStringNotContainsString($proxyFile . ':3', $formatted);
            $this->assertStringNotContainsString(':420', $formatted);
            $this->assertSame(realpath($proxyFile), $exception->getFile());
            $this->assertSame(3, $exception->getLine());
            $this->assertSame(
                ['file' => $originFile, 'line' => 42],
                LineMapFixer::resolveLocation($proxyFile, 3)
            );
        } finally {
            @unlink($proxyFile);
            @unlink($directory . '/line-map.php');
            @rmdir($directory);
        }
    }
}
