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

namespace HyperfTest\ExceptionHandler;

use Hyperf\Context\Context;
use Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\Nats\Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function json_decode;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class WhoopsExceptionHandlerTest extends TestCase
{
    public function testPlainTextWhoops()
    {
        Context::set(ServerRequestInterface::class, new Request('GET', '/'));
        $handler = new WhoopsExceptionHandler();
        $response = $handler->handle(new Exception(), new Response());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/plain', $response->getHeader('Content-Type')[0]);
    }

    public function testPlainTextWhoopsResolvesProxyLocationWithoutModifyingThrowable(): void
    {
        Context::set(ServerRequestInterface::class, new Request('GET', '/'));
        $directory = sys_get_temp_dir() . '/hyperf-whoops-line-map-' . uniqid();
        mkdir($directory);
        $proxyFile = $directory . '/App_WhoopsLineMapStub.proxy.php';
        $originFile = $directory . '/WhoopsLineMapStub.php';
        file_put_contents($proxyFile, "<?php\n\nthrow new \\RuntimeException('line map');\n");
        file_put_contents($directory . '/line-map.php', sprintf(
            "<?php\nreturn ['maps' => ['App\\\\WhoopsLineMapStub' => ['file' => %s, 'ranges' => [[3, 3, 42, 42]], 'methods' => []]]];\n",
            var_export($originFile, true)
        ));

        try {
            require $proxyFile;
            $this->fail('The proxy fixture must throw an exception.');
        } catch (RuntimeException $exception) {
            $handler = new WhoopsExceptionHandler();
            $response = $handler->handle($exception, new Response());
            $content = (string) $response->getBody();

            $this->assertStringContainsString($originFile . ' on line 42', $content);
            $this->assertStringNotContainsString('WhoopsLineMapStub.proxy.php', $content);
            $this->assertSame(realpath($proxyFile), $exception->getFile());
            $this->assertSame(3, $exception->getLine());
        } finally {
            @unlink($proxyFile);
            @unlink($directory . '/line-map.php');
            @rmdir($directory);
        }
    }

    public function testHtmlWhoops()
    {
        $request = new Request('GET', '/');
        $request = $request->withHeader('accept', ['text/html,application/json,application/xml']);
        Context::set(ServerRequestInterface::class, $request);
        $handler = new WhoopsExceptionHandler();
        $response = $handler->handle(new Exception(), new Response());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('text/html', $response->getHeader('Content-Type')[0]);
    }

    public function testJsonWhoops()
    {
        $request = new Request('GET', '/');
        $request = $request->withHeader('accept', ['application/json,application/xml']);
        Context::set(ServerRequestInterface::class, $request);
        $handler = new WhoopsExceptionHandler();
        $response = $handler->handle(new Exception(), new Response());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeader('Content-Type')[0]);
        $arr = json_decode($response->getBody()->__toString(), true);
        $this->assertArrayHasKey('trace', $arr['error']);
    }

    public function testXmlWhoops()
    {
        $request = new Request('GET', '/');
        $request = $request->withHeader('accept', ['application/xml']);
        Context::set(ServerRequestInterface::class, $request);
        $handler = new WhoopsExceptionHandler();
        $response = $handler->handle(new Exception(), new Response());
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('application/xml', $response->getHeader('Content-Type')[0]);
    }
}
