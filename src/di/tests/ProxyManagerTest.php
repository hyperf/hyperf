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

namespace HyperfTest\Di;

use Hyperf\Di\Aop\ProxyManager;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ProxyManagerTest extends TestCase
{
    public function testLineMapCacheValidation(): void
    {
        $directory = sys_get_temp_dir() . '/hyperf-proxy-manager-' . uniqid();
        mkdir($directory);
        $mapFile = $directory . '/line-map.php';

        try {
            $this->assertFalse(ProxyManager::isLineMapCacheValid($directory, true));
            $this->assertTrue(ProxyManager::isLineMapCacheValid($directory, false));

            $legacyProxy = $directory . '/App_Foo.proxy.php';
            file_put_contents($legacyProxy, '<?php /* __hyperf_exception__ */');
            $this->assertFalse(ProxyManager::isLineMapCacheValid($directory, false));
            unlink($legacyProxy);

            file_put_contents($mapFile, "<?php\nreturn ['legacy' => []];\n");
            $this->assertFalse(ProxyManager::isLineMapCacheValid($directory, true));
            $this->assertFalse(ProxyManager::isLineMapCacheValid($directory, false));

            file_put_contents($mapFile, "<?php\nreturn ['version' => 2, 'maps' => []];\n");
            $this->assertFalse(ProxyManager::isLineMapCacheValid($directory, true));

            file_put_contents($mapFile, sprintf(
                "<?php\nreturn ['version' => %d, 'maps' => []];\n",
                ProxyManager::LINE_MAP_VERSION
            ));
            $this->assertTrue(ProxyManager::isLineMapCacheValid($directory, true));
        } finally {
            @unlink($directory . '/App_Foo.proxy.php');
            @unlink($mapFile);
            @rmdir($directory);
        }
    }
}
