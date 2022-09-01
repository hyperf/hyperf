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
namespace HyperfTest\GrpcClient;

use Google\Protobuf\GPBEmpty;
use Google\Protobuf\Internal\Message;
use Hyperf\Di\Container;
use Hyperf\GrpcClient\Exception\GrpcClientException;
use Hyperf\GrpcClient\StreamingCall;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\ChannelPool;
use HyperfTest\GrpcClient\Stub\RouteGuideClient;
use PHPUnit\Framework\TestCase;
use Routeguide\Point;
use Routeguide\Rectangle;
use Routeguide\RouteNote;
use Routeguide\RouteSummary;

/**
 * @internal
 * @coversNothing
 */
class RouteGuideClientTest extends TestCase
{
    protected function setUp(): void
    {
        $container = \Mockery::mock(Container::class);
        $container->shouldReceive('get')->with(ChannelPool::class)->andReturn(new ChannelPool());
        $container->shouldReceive('has')->andReturn(false);
        ApplicationContext::setContainer($container);
    }

    public function testGrpcRouteGuideGetFeature()
    {
        $client = new RouteGuideClient('127.0.0.1:50051', ['retry_attempts' => 0]);

        $point = new Point();
        $point->setLatitude(407838351);
        $point->setLongitude(-746143763);
        [$feature,] = $client->getFeature($point);
        $this->assertEquals('Patriots Path, Mendham, NJ 07945, USA', $feature->getName());
    }

    public function testGrpcRouteGuideListFeatures()
    {
        $client = new RouteGuideClient('127.0.0.1:50051', ['retry_attempts' => 0]);

        $hi = new Point();
        $hi->setLatitude(420000000);
        $hi->setLongitude(-730000000);

        $lo = new Point();
        $lo->setLatitude(400000000);
        $lo->setLongitude(-750000000);

        $rect = new Rectangle();
        $rect->setHi($hi);
        $rect->setLo($lo);

        /** @var StreamingCall $call */
        $call = $client->listFeatures();
        $call->send($rect);
        $response = $call->recv();
        $this->assertEquals('Patriots Path, Mendham, NJ 07945, USA', $response->message->getName());
        $response = $call->recv();
        $this->assertEquals('101 New Jersey 10, Whippany, NJ 07981, USA', $response->message->getName());
        $response = $call->recv();
        $this->assertTrue($response->message instanceof Message);
        while (true) {
            $response = $call->recv();
            if ($response->message instanceof GPBEmpty) {
                break;
            }
        }
        $this->assertFalse($response->rawResponse->pipeline);
    }

    public function testGrpcRouteGuideRecordRoute()
    {
        $client = new RouteGuideClient('127.0.0.1:50051', ['retry_attempts' => 0]);

        $first = new Point();
        $first->setLatitude(1);
        $first->setLongitude(1);

        $second = new Point();
        $second->setLatitude(2);
        $second->setLongitude(2);

        $call = $client->recordRoute();
        $call->push($first);
        $call->push($second);
        $call->end();
        /** @var RouteSummary $summary */
        $response = $call->recv();
        $summary = $response->message;
        $this->assertEquals(2, $summary->getPointCount());
    }

    public function testGrpcRouteGuideRouteChat()
    {
        $client = new RouteGuideClient('127.0.0.1:50051', ['retry_attempts' => 0]);
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

        $call = $client->routeChat();
        $call->push($firstNote);
        // 第一个点应该无法收到回复
        $this->expectException(GrpcClientException::class);
        $call->recv(1);
        $call->push($firstNote);
        /** @var RouteNote $note */
        $response = $call->recv();
        $note = $response->message;
        $this->assertEquals($first->getLatitude(), $note->getLocation()->getLatitude());

        $call->push($secondNote);
        $call->push($secondNote);
        $response = $call->recv();
        $note = $response->message;
        $this->assertEquals($second->getLatitude(), $note->getLocation()->getLatitude());
    }
}
