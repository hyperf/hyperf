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
namespace HyperfTest\Http2Client;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\Http2Client\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        CoordinatorManager::until('HTTP2ClientUnit')->resume();
    }

    public function testHTTP2ClientLoop()
    {
        $client = $this->getClient('http://127.0.0.1:10002');

        for ($i = 0; $i < 100; ++$i) {
            $callbacks[] = static function () use ($client) {
                $response = $client->request(new Request('/', body: $id = uniqid()));
                return (int) ($response->getBody() === $id);
            };
        }

        $result = parallel($callbacks);
        $this->assertSame(100, array_sum($result));
        $client->close();
    }

    protected function getClient(string $baseUri)
    {
        $client = new Client($baseUri);
        $ref = new ReflectionClass($client);
        $identifier = $ref->getProperty('identifier');
        $identifier->setAccessible(true);
        $identifier->setValue($client, 'HTTP2ClientUnit');
        return $client;
    }
}
