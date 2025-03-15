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

namespace HyperfTest\Context;

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Waiter;
use Hyperf\Engine\Channel;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use Swow\Psr7\Message\ResponsePlusInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

use function Hyperf\Coroutine\go;
use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ContextTest extends TestCase
{
    public function testOverride()
    {
        Context::set('override.id', 1);

        $this->assertSame(2, Context::override('override.id', function ($id) {
            return $id + 1;
        }));

        $this->assertSame(2, Context::get('override.id'));
    }

    public function testGetOrSet()
    {
        Context::set('test.store.id', null);
        $this->assertSame(1, Context::getOrSet('test.store.id', function () {
            return 1;
        }));
        $this->assertSame(1, Context::getOrSet('test.store.id', function () {
            return 2;
        }));

        Context::set('test.store.id', null);
        $this->assertSame(1, Context::getOrSet('test.store.id', 1));
    }

    public function testCopy()
    {
        Context::set('test.store.id', $uid = uniqid());
        $id = Coroutine::id();
        parallel([
            function () use ($id, $uid) {
                Context::copy($id, ['test.store.id']);
                $this->assertSame($uid, Context::get('test.store.id'));
            },
        ]);
    }

    public function testCopyAfterSet()
    {
        Context::set('test.store.id', $uid = uniqid());
        $id = Coroutine::id();
        parallel([
            function () use ($id, $uid) {
                Context::set('test.store.name', 'Hyperf');
                Context::copy($id, ['test.store.id']);
                $this->assertSame($uid, Context::get('test.store.id'));

                // TODO: Context::copy will delete origin values.
                $this->assertNull(Context::get('test.store.name'));
            },
        ]);
    }

    public function testContextChangeAfterCopy()
    {
        $obj = new stdClass();
        $obj->id = $uid = uniqid();

        Context::set('test.store.id', $obj);
        Context::set('test.store.useless.id', 1);
        $id = Coroutine::id();
        $tid = uniqid();
        parallel([
            function () use ($id, $uid, $tid) {
                Context::copy($id, ['test.store.id']);
                $obj = Context::get('test.store.id');
                $this->assertSame($uid, $obj->id);
                $obj->id = $tid;
                $this->assertFalse(Context::has('test.store.useless.id'));
            },
        ]);

        $this->assertSame($tid, Context::get('test.store.id')->id);
    }

    public function testContextFromNull()
    {
        $res = Context::get('id', $default = 'Hello World!', -1);
        $this->assertSame($default, $res);

        $res = Context::get('id', null, -1);
        $this->assertSame(null, $res);

        $this->assertFalse(Context::has('id', -1));

        Context::copy(-1);

        parallel([
            function () {
                Context::set('id', $id = uniqid());
                Context::copy(-1, ['id']);
                $this->assertSame($id, Context::get('id'));
            },
        ]);
    }

    public function testContextDestroy()
    {
        Context::set($id = uniqid(), $value = uniqid());

        $this->assertSame($value, Context::get($id));
        Context::destroy($id);
        $this->assertNull(Context::get($id));
    }

    public function testRequestContext()
    {
        $request = Mockery::mock(ServerRequestPlusInterface::class);
        RequestContext::set($request);
        $this->assertSame($request, RequestContext::get());

        Context::set(ServerRequestInterface::class, $req = Mockery::mock(ServerRequestPlusInterface::class));
        $this->assertNotSame($request, RequestContext::get());
        $this->assertSame($req, RequestContext::get());
        $this->assertSame($req, Context::get(ServerRequestInterface::class));
    }

    public function testResponseContext()
    {
        $response = Mockery::mock(ResponsePlusInterface::class);
        ResponseContext::set($response);
        $this->assertSame($response, ResponseContext::get());

        Context::set(ResponseInterface::class, $req = Mockery::mock(ResponsePlusInterface::class));
        $this->assertNotSame($response, ResponseContext::get());
        $this->assertSame($req, ResponseContext::get());
        $this->assertSame($req, Context::get(ResponseInterface::class));
    }

    public function testResponseContextWithCoroutineId()
    {
        $response = Mockery::mock(ResponsePlusInterface::class);
        $chan = new Channel(1);
        $close = new Channel(1);
        go(function () use ($chan, $response, $close) {
            ResponseContext::set($response);
            $this->assertSame($response, ResponseContext::get());
            $chan->push(Coroutine::id());
            $close->pop(1);
        });

        $id = $chan->pop(5);
        $this->assertSame($response, ResponseContext::get($id));
        $close->push(true);
    }

    public function testRequestContextWithCoroutineId()
    {
        $request = Mockery::mock(ServerRequestPlusInterface::class);
        RequestContext::set($request);
        $id = Coroutine::id();
        (new Waiter())->wait(function () use ($id, $request) {
            $this->assertSame($request, RequestContext::get($id));
        });
    }

    public function testContextOverrideWithCoroutineId()
    {
        $id = Coroutine::id();
        $value = uniqid();
        Context::override('override.id.coroutine_id', fn () => $value);
        (new Waiter())->wait(function () use ($id, $value) {
            Context::override(
                'override.id.coroutine_id',
                function ($v) use ($value) {
                    $this->assertSame($v, $value);
                    return '123';
                },
                $id
            );
        });

        $this->assertSame('123', Context::get('override.id.coroutine_id'));
    }

    public function testContextGetOrSetWithCoroutineId()
    {
        $id = Coroutine::id();
        $value = uniqid();
        Context::getOrSet('get_or_set.id.coroutine_id', fn () => $value);
        (new Waiter())->wait(function () use ($id, $value) {
            $res = Context::getOrSet('get_or_set.id.coroutine_id', fn () => '123', $id);
            $this->assertSame($res, $value);
        });
    }

    public function testIncrementInNonCoroutineEnvironment()
    {
        $id = 'test.increment.non.coroutine';
        Context::destroy($id);

        // 测试初始递增
        $result = Context::increment($id);
        $this->assertSame(1, $result);

        // 测试再次递增
        $result = Context::increment($id);
        $this->assertSame(2, $result);

        // 测试指定步长递增
        $result = Context::increment($id, 3);
        $this->assertSame(5, $result);
    }

    public function testDecrementInNonCoroutineEnvironment()
    {
        $id = 'test.decrement.non.coroutine';
        Context::destroy($id);

        // 测试不存在的键递减
        $result = Context::decrement($id);
        $this->assertSame(-1, $result);

        // 测试已存在的键递减
        $result = Context::decrement($id);
        $this->assertSame(-2, $result);

        // 测试指定步长递减
        $result = Context::decrement($id, 3);
        $this->assertSame(-5, $result);
    }

    public function testIncrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // 测试初始递增
            $id = 'test.co.increment';
            $result = Context::increment($id);
            $this->assertSame(1, $result);

            // 测试再次递增
            $result = Context::increment($id);
            $this->assertSame(2, $result);

            // 测试指定步长递增
            $result = Context::increment($id, 3);
            $this->assertSame(5, $result);
        });
    }

    public function testDecrementInCoroutineEnvironment()
    {
        (new Waiter())->wait(function () {
            // 测试初始递减
            $id = 'test.co.decrement';
            $result = Context::decrement($id);
            $this->assertSame(-1, $result);

            // 测试再次递减
            $result = Context::decrement($id);
            $this->assertSame(-2, $result);

            // 测试指定步长递减
            $result = Context::decrement($id, 3);
            $this->assertSame(-5, $result);
        });
    }

    public function testIncrementWithSpecificCoroutineId()
    {
        $cid = Coroutine::id();
        $id = 'test.specific.increment';
        Context::set($id, 1);
        (new Waiter())->wait(function () use ($id, $cid) {
            $result = Context::increment($id, 1, $cid);
            $this->assertSame(2, $result);
        });
    }

    public function testDecrementWithSpecificCoroutineId()
    {
        $cid = Coroutine::id();
        $id = 'test.specific.decrement';
        Context::set($id, 3);
        (new Waiter())->wait(function () use ($id, $cid) {
            // 测试特定协程ID的递减
            $result = Context::decrement($id, 2, $cid);
            $this->assertSame(1, $result);
        });
    }

    public function testMacro()
    {
        Context::macro('macroTest', function (string $value) {
            return 'macro-' . $value;
        });

        $this->assertTrue(Context::hasMacro('macroTest'));
        $this->assertFalse(Context::hasMacro('notExistMacro'));
        $this->assertSame('macro-test', Context::macroTest('test'));
    }

    public function testMacroWithCoroutine()
    {
        Context::macro('macroCoroutine', function (string $value) {
            return 'coroutine-' . $value;
        });

        (new Waiter())->wait(function () {
            $this->assertTrue(Context::hasMacro('macroCoroutine'));
            $this->assertSame('coroutine-test', Context::macroCoroutine('test'));
        });
    }

    public function testMixin()
    {
        Context::mixin(new Stub\ContextMixin());

        $this->assertTrue(Context::hasMacro('mixinMethod'));
        $this->assertTrue(Context::hasMacro('protectedMixinMethod'));
        $this->assertSame('mixin-test', Context::mixinMethod('test'));
        $this->assertSame('protected-test', Context::protectedMixinMethod('test'));
    }

    public function testMixinWithoutReplace()
    {
        Context::macro('mixinMethod', function (string $value) {
            return 'original-' . $value;
        });

        Context::mixin(new Stub\ContextMixin(), false);

        $this->assertSame('original-test', Context::mixinMethod('test'));
        $this->assertSame('protected-test', Context::protectedMixinMethod('test'));
    }
}
