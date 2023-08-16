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
namespace HyperfTest\Session;

use Hyperf\Session\Handler\FileHandler;
use Hyperf\Session\Handler\NullHandler;
use Hyperf\Session\Session;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \Hyperf\Session\Session
 */
class SessionTest extends TestCase
{
    public function testSession()
    {
        $id = Str::random(40);
        $session = new Session($name = 'HYPERF_SESSION_ID', new NullHandler(), $id);
        $this->assertSame($name, $session->getName());
        $this->assertSame($id, $session->getId());
        $this->assertTrue($session->isValidId($id));

        $session = new Session('HYPERF_SESSION_ID', new NullHandler());
        $this->assertTrue($session->isValidId($session->getId()));
    }

    public function testSessionAttributes()
    {
        $id = Str::random(40);
        $session = new Session('HYPERF_SESSION_ID', new NullHandler(), $id);
        $data = [
            'int' => 1,
            'true' => true,
            'false' => false,
            'float' => 1.23,
            'string' => 'foo',
        ];
        foreach ($data as $key => $value) {
            $session->set($key, $value);
        }
        foreach ($data as $key => $value) {
            $this->assertTrue($session->has($key));
            $this->assertSame($value, $session->get($key));
        }
        $this->assertFalse($session->has('not-exist'));
        $this->assertSame($data, $session->all());

        foreach ($data as $key => $value) {
            $this->assertSame($value, $session->remove($key));
        }

        $session->put($data);
        $this->assertSame($data, $session->all());

        $session->clear();
        $this->assertSame([], $session->all());

        $session->put('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $session->all());

        $session->put($data);
        $session->forget('foo');
        $this->assertSame($data, $session->all());

        $session->replace(['int' => 2, 'foo' => 'baz']);
        $this->assertSame([
            'int' => 2,
            'true' => true,
            'false' => false,
            'float' => 1.23,
            'string' => 'foo',
            'foo' => 'baz',
        ], $session->all());
    }

    public function testSessionPush()
    {
        $session = new Session('HYPERF_SESSION_ID', new NullHandler());
        $session->push('foo', 'bar');
        $this->assertSame([
            'foo' => ['bar'],
        ], $session->all());
        $session->push('foo', 'baz');
        $this->assertSame([
            'foo' => ['bar', 'baz'],
        ], $session->all());
    }

    public function testToken()
    {
        $session = new Session('HYPERF_SESSION_ID', new NullHandler());
        $this->assertSame('', $session->token());

        $token = $session->regenerateToken();
        $this->assertSame($token, $session->token());
        $this->assertTrue($session->isValidId($token));
    }

    public function testPreviousUrl()
    {
        $session = new Session('HYPERF_SESSION_ID', new NullHandler());
        $url = 'http://127.0.0.1:9501/foo/bar';
        $session->setPreviousUrl($url);
        $this->assertSame($url, $session->previousUrl());

        $session->set('_previous.url', 123);
        $this->assertNull($session->previousUrl());
    }

    public function testStartSession()
    {
        $fileHandler = new FileHandler(new Filesystem(), '/tmp', 1);
        $session = new Session('HYPERF_SESSION_ID', $fileHandler);
        $this->assertTrue($session->start());
        $this->assertTrue($session->isStarted());

        $id = $session->getId();
        $session->set('foo', 'bar');
        $session->save();
        $this->assertFileExists('/tmp/' . $id);

        $session = new Session('HYPERF_SESSION_ID', $fileHandler, $id);
        $session->start();
        $this->assertSame('bar', $session->get('foo'));

        $this->assertTrue($session->invalidate());
        $this->assertFileDoesNotExist('/tmp/' . $id);
    }

    public function testFlash()
    {
        $fileHandler = new FileHandler(new Filesystem(), '/tmp', 1);
        $session = new Session('HYPERF_SESSION_ID', $fileHandler);
        $id = $session->getId();
        $session->flash('foo', 'bar');
        $this->assertSame([
            'foo' => 'bar',
            '_flash' => [
                'new' => ['foo'],
                'old' => [],
            ],
        ], $session->all());
        $session->save();

        $session = new Session('HYPERF_SESSION_ID', $fileHandler, $id);
        $this->assertSame([], $session->all());
    }
}
