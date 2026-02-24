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

namespace HyperfTest\Devtool\Generator;

use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class GeneratorCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPathWithRelativePath(): void
    {
        $command = new GeneratorCommandStub();

        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->with('path')
            ->andReturn('packages/my-package/src');
        $command->setTestInput($input);

        $path = $command->exposedGetPath('MyNamespace\MyClass');

        $this->assertSame(BASE_PATH . '/packages/my-package/src/MyClass.php', $path);
    }

    public function testGetPathWithRelativePathWithTrailingSlash(): void
    {
        $command = new GeneratorCommandStub();

        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->with('path')
            ->andReturn('packages/my-package/src/');
        $command->setTestInput($input);

        $path = $command->exposedGetPath('MyNamespace\MyClass');

        $this->assertSame(BASE_PATH . '/packages/my-package/src/MyClass.php', $path);
    }

    public function testGetPathWithAbsolutePath(): void
    {
        $command = new GeneratorCommandStub();

        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->with('path')
            ->andReturn('/tmp/custom-path');
        $command->setTestInput($input);

        $path = $command->exposedGetPath('MyNamespace\MyClass');

        $this->assertSame('/tmp/custom-path/MyClass.php', $path);
    }

    public function testGetPathWithAbsolutePathWithTrailingSlash(): void
    {
        $command = new GeneratorCommandStub();

        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->with('path')
            ->andReturn('/tmp/custom-path/');
        $command->setTestInput($input);

        $path = $command->exposedGetPath('MyNamespace\MyClass');

        $this->assertSame('/tmp/custom-path/MyClass.php', $path);
    }

    public function testGetPathExtractsClassNameFromDeeplyNestedNamespace(): void
    {
        $command = new GeneratorCommandStub();

        $input = Mockery::mock(InputInterface::class);
        $input->shouldReceive('getOption')
            ->with('path')
            ->andReturn('src/Controllers');
        $command->setTestInput($input);

        $path = $command->exposedGetPath('App\Http\Controllers\Api\V1\UserController');

        $this->assertSame(BASE_PATH . '/src/Controllers/UserController.php', $path);
    }

    public function testPathOptionIsRegistered(): void
    {
        $command = new GeneratorCommandStub();

        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('path'));
        $this->assertFalse($definition->getOption('path')->isValueRequired());
        $this->assertNull($definition->getOption('path')->getDefault());
    }
}
