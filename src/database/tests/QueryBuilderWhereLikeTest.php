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

namespace HyperfTest\Database;

use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Register;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use Hyperf\DbConnection\Db;
use Hyperf\Testing\TestCase;
use HyperfTest\Database\Stubs\ContainerStub;
use Mockery;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class QueryBuilderWhereLikeTest extends TestCase
{
    protected function setUp(): void
    {
        $container = ContainerStub::getContainer();
        $db = new Db($container);
        $resolver = $container->get(ConnectionResolverInterface::class);
        $container->shouldReceive('get')->with(Db::class)->andReturn($db);
        Register::setConnectionResolver($resolver);
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->dropSchema();
        Mockery::close();
        $reflection = new ReflectionClass(ApplicationContext::class);
        $reflection->setStaticPropertyValue('container', null);
    }

    public function testWhereLike()
    {
        $users = Db::table('users')->whereLike('email', 'john.doe@example.com')->get();
        $this->assertCount(1, $users);
        $this->assertSame('John.Doe@example.com', $users[0]->email);

        $this->assertSame(4, Db::table('users')->whereNotLike('email', 'john.doe@example.com')->count());
    }

    public function testWhereLikeWithPercentWildcard()
    {
        $this->assertSame(5, Db::table('users')->whereLike('email', '%@example.com')->count());
        $this->assertSame(2, Db::table('users')->whereNotLike('email', '%Doe%')->count());

        $users = Db::table('users')->whereLike('email', 'john%')->get();
        $this->assertCount(1, $users);
        $this->assertSame('John.Doe@example.com', $users[0]->email);
    }

    public function testWhereLikeWithUnderscoreWildcard()
    {
        $users = Db::table('users')->whereLike('email', '_a_e_%@example.com')->get();
        $this->assertCount(2, $users);
        $this->assertSame('janedoe@example.com', $users[0]->email);
        $this->assertSame('Dale.Doe@example.com', $users[1]->email);
    }

    public function testWhereLikeCaseSensitive()
    {
        $users = Db::table('users')->whereLike('email', 'john.doe@example.com', true)->get();
        $this->assertCount(0, $users);

        $users = Db::table('users')->whereLike('email', 'tim.smith@example.com', true)->get();
        $this->assertCount(1, $users);
        $this->assertSame('tim.smith@example.com', $users[0]->email);
        $this->assertSame(5, Db::table('users')->whereNotLike('email', 'john.doe@example.com', true)->count());
    }

    public function testWhereLikeWithPercentWildcardCaseSensitive()
    {
        $this->assertSame(2, Db::table('users')->whereLike('email', '%Doe@example.com', true)->count());
        $this->assertSame(4, Db::table('users')->whereNotLike('email', '%smith%', true)->count());

        $users = Db::table('users')->whereLike('email', '%Doe@example.com', true)->get();
        $this->assertCount(2, $users);
        $this->assertSame('John.Doe@example.com', $users[0]->email);
        $this->assertSame('Dale.Doe@example.com', $users[1]->email);
    }

    public function testWhereLikeWithUnderscoreWildcardCaseSensitive()
    {
        $users = Db::table('users')->whereLike('email', 'j__edoe@example.com', true)->get();
        $this->assertCount(1, $users);
        $this->assertSame('janedoe@example.com', $users[0]->email);

        $users = Db::table('users')->whereNotLike('email', '%_oe@example.com', true)->get();
        $this->assertCount(2, $users);
        $this->assertSame('Earl.Smith@example.com', $users[0]->email);
        $this->assertSame('tim.smith@example.com', $users[1]->email);
    }

    protected function createSchema(): void
    {
        Schema::create('like_users', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 200);
            $table->text('email');
        });

        Db::table('like_users')->insert([
            ['name' => 'John Doe', 'email' => 'John.Doe@example.com'],
            ['name' => 'Jane Doe', 'email' => 'janedoe@example.com'],
            ['name' => 'Dale doe', 'email' => 'Dale.Doe@example.com'],
            ['name' => 'Earl Smith', 'email' => 'Earl.Smith@example.com'],
            ['name' => 'tim smith', 'email' => 'tim.smith@example.com'],
        ]);
    }

    protected function dropSchema(): void
    {
        Schema::drop('like_users');
    }
}
