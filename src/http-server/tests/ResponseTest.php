<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpServer;

use Hyperf\HttpServer\Response;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @internal
 * @coversNothing
 */
class ResponseTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
        Context::set(PsrResponseInterface::class, null);
    }

    public function testRedirect()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $psrResponse = new \Hyperf\HttpMessage\Base\Response();
        Context::set(PsrResponseInterface::class, $psrResponse);

        $response = new Response();
        $res = $response->redirect('https://www.baidu.com');

        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('https://www.baidu.com', $res->getHeaderLine('Location'));

        $response = new Response();
        $res = $response->redirect('http://www.baidu.com');

        $this->assertSame(302, $res->getStatusCode());
        $this->assertSame('http://www.baidu.com', $res->getHeaderLine('Location'));
    }
}
