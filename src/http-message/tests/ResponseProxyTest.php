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
namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Server\Response;
use HyperfTest\HttpMessage\Stub\Server\ResponseStub;

/**
 * @internal
 * @coversNothing
 */
class ResponseProxyTest extends ResponseTest
{
    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testStatusCode()
    {
        parent::testStatusCode();
    }

    public function testHeaders()
    {
        parent::testHeaders();
    }

    public function testCookies()
    {
        parent::testCookies();
    }

    protected function newResponse()
    {
        $response = new ResponseStub();
        $response->setResponse(new Response());
        return $response;
    }
}
