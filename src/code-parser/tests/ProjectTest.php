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
namespace HyperfTest\CodeParser;

use Hyperf\CodeParser\Project;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ProjectTest extends TestCase
{
    public function testNamespaceFor()
    {
        $mock = $this->createProject();
        $ns = $mock->namespace('app/Model');
        $this->assertEquals('App\\Model\\', $ns);
    }

    public function testClassNameFor()
    {
        $mock = $this->createProject();
        $ns = $mock->className('app/Model/User.php');
        $this->assertEquals('App\\Model\\User', $ns);
    }

    public function testPathForClass()
    {
        $mock = $this->createProject();
        $path = $mock->path('App\\Model\\Foo');
        $this->assertEquals('app/Model/Foo.php', $path);
    }

    public function testPathForNamespace()
    {
        $mock = $this->createProject();
        $path = $mock->path('App\\Model\\');
        $this->assertEquals('app/Model/', $path);
    }

    public function testPathForNoExtension()
    {
        $mock = $this->createProject();
        $path = $mock->path('App\\Model', '');
        $this->assertEquals('app/Model', $path);
    }

    private function createProject(): Project
    {
        $mock = Mockery::mock(Project::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mock->shouldReceive('getAutoloadRules')
            ->andReturn([
                'App\\' => 'app/',
            ]);
        return $mock;
    }
}
