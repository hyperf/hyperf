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

namespace HyperfTest\Phar;

use Hyperf\Phar\Package;
use Hyperf\Phar\PharBuilder;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PackageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testDefaults()
    {
        $package = new Package([], 'dirs/');

        $this->assertEquals([], $package->getBins());
        $this->assertEquals('dirs/', $package->getDirectory());
        $this->assertEquals(null, $package->getName());
        $this->assertEquals('dirs', $package->getShortName());
        $this->assertEquals('vendor/', $package->getVendorPath());
    }

    public function testPackage()
    {
        $package = new Package([
            'name' => 'hyperf/phar',
            'bin' => ['bin/hyperf.php', 'bin/phar.php'],
            'config' => [
                'vendor-dir' => 'src/vendors',
            ],
        ], 'dirs/');
        $this->assertEquals(['bin/hyperf.php', 'bin/phar.php'], $package->getBins());
        $this->assertEquals('hyperf/phar', $package->getName());
        $this->assertEquals('phar', $package->getShortName());
        $this->assertEquals('src/vendors/', $package->getVendorPath());
    }

    public function testBundleWillContainComposerJsonButNotVendor()
    {
        $dir = realpath(__DIR__ . '/fixtures/03-project-with-phars') . '/';
        $package = new Package([
            'config' => [
                'vendor-dir' => 'vendors',
            ],
        ], $dir);
        $bundle = $package->bundle();

        $this->assertTrue($bundle->checkContains($dir . 'composer.json'));
        $this->assertFalse($bundle->checkContains($dir . 'vendor/autoload.php'));
        $this->assertFalse($bundle->checkContains($dir . 'composer.phar'));
        $this->assertTrue($bundle->checkContains($dir . 'phar-composer.phar'));
    }

    public function testBundleWillNotContainComposerPharInRoot()
    {
        $dir = realpath(__DIR__ . '/fixtures/03-project-with-phars') . '/';
        $package = new Package([
            'config' => [
                'vendor-dir' => 'vendors',
            ],
        ], $dir);
        $bundle = $package->bundle();

        $this->assertFalse($bundle->checkContains($dir . 'composer.phar'));
        $this->assertTrue($bundle->checkContains($dir . 'phar-composer.phar'));
    }

    public function testBundleWillContainComposerPharFromSrc()
    {
        $dir = realpath(__DIR__ . '/fixtures/04-project-with-phars-in-src') . '/';
        $package = new Package([
            'config' => [
                'vendor-dir' => 'vendors',
            ],
        ], $dir);
        $bundle = $package->bundle();

        $this->assertTrue($bundle->checkContains($dir . 'composer.json'));
        $this->assertTrue($bundle->checkContains($dir . 'src/composer.phar'));
        $this->assertTrue($bundle->checkContains($dir . 'src/phar-composer.phar'));
    }

    public function testInstallPathWhenGetPackagesDependencies()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $builder = new PharBuilder(__DIR__ . '/fixtures/07-composer-versions/composer.lock', $logger);
        $packages = $builder->getPackagesDependencies();
        $this->assertSame('hyperf/engine', $packages[0]->getName());
        $this->assertStringContainsString('/vendor/hyperf/engine/', $packages[0]->getDirectory());
    }
}
