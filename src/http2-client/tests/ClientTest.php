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

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Http\V2\Request;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Grpc\Parser;
use Hyperf\Http2Client\Client;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Routeguide\Point;
use Routeguide\RouteNote;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class ClientTest extends TestCase
{
    public function setUp(): void
    {
        ApplicationContext::setContainer($container = Mockery::mock(ContainerInterface::class));
        $container->shouldReceive('has')->with(StdoutLoggerInterface::class)->andReturnFalse();
        $container->shouldReceive('has')->with(FormatterInterface::class)->andReturnFalse();
    }

    protected function tearDown(): void
    {
        Mockery::close();
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
        sleep(2);
    }

    public function testHTTP2Pipeline()
    {
        if (SWOOLE_VERSION_ID < 50000) {
            $this->markTestSkipped('');
        }

        $client = $this->getClient('127.0.0.1:50051');
        $num = rand(0, 1000000);

        $first = new Point();
        $first->setLatitude($num);
        $first->setLongitude($num);

        $firstNote = new RouteNote();
        $firstNote->setLocation($first);
        $firstNote->setMessage('hello');

        $second = new Point();
        $second->setLatitude($num + 1);
        $second->setLongitude($num + 1);

        $secondNote = new RouteNote();
        $secondNote->setLocation($second);
        $secondNote->setMessage('world');

        $streamId = $client->send(new Request(
            '/routeguide.RouteGuide/RouteChat',
            'POST',
            '',
            [
                'content-type' => 'application/grpc+proto',
                'te' => 'trailers',
                'user-agent' => 'HyperfClient',
            ],
            true
        ));

        $client->write($streamId, Parser::serializeMessage($firstNote));
        $client->write($streamId, Parser::serializeMessage($firstNote));

        $res = $client->recv($streamId, 10);
        $this->assertSame(200, $res->getStatusCode());

        $client->write($streamId, Parser::serializeMessage($secondNote));
        $client->write($streamId, Parser::serializeMessage($secondNote));
        $res = $client->recv($streamId, 10);
        $this->assertSame(0, $res->getStatusCode());

        $client->close();
        sleep(2);
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
