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
namespace HyperfTest\Nsq;

use Hyperf\Config\Config;
use Hyperf\Nsq\Api\Channel;
use Hyperf\Nsq\Api\HttpClient;
use Hyperf\Nsq\Api\Topic;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ApiTest extends TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }

    public function testTopic()
    {
        $client = new Topic($this->getClient());
        $this->assertTrue($client->create('hyperf.test'));
        $this->assertTrue($client->pause('hyperf.test'));
        $this->assertTrue($client->unpause('hyperf.test'));
        $this->assertTrue($client->empty('hyperf.test'));
        $this->assertTrue($client->delete('hyperf.test'));
    }

    public function testChannel()
    {
        $client = new Topic($this->getClient());
        $client->create('hyperf.test');

        $client = new Channel($this->getClient());
        $this->assertTrue($client->create('hyperf.test', 'test'));
        $this->assertTrue($client->pause('hyperf.test', 'test'));
        $this->assertTrue($client->unpause('hyperf.test', 'test'));
        $this->assertTrue($client->empty('hyperf.test', 'test'));
        $this->assertTrue($client->delete('hyperf.test', 'test'));
    }

    protected function getClient()
    {
        $config = new Config([]);
        return new HttpClient($config);
    }
}
