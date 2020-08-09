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
namespace HyperfTest\Jet;

use Hyperf\Jet\ClientFactory;
use Hyperf\Jet\DataFormatter\DataFormatter;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Jet\ProtocolManager;
use Hyperf\Jet\ServiceManager;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class IntegrationTest extends TestCase
{
    protected $host = '127.0.0.1';

    protected $port = 9503;

    protected function setUp()
    {
        $this->markTestSkipped('This test needs a complete RPC Server.');
    }

    public function testJsonrpc()
    {
        $protocol = 'jsonrpc';
        ProtocolManager::register($protocol, [
            ProtocolManager::TRANSPORTER => new StreamSocketTransporter(),
            ProtocolManager::PACKER => new JsonEofPacker(),
            ProtocolManager::PATH_GENERATOR => new PathGenerator(),
            ProtocolManager::DATA_FORMATTER => new DataFormatter(),
        ]);
        $service = 'CalculatorService';
        ServiceManager::register($service, $protocol, [
            ServiceManager::NODES => [
                [$this->host, $this->port],
            ],
        ]);
        $clientFactory = new ClientFactory();
        $client = $clientFactory->create($service, $protocol);
        $result = $client->add($a = 1, $b = 2);
        $this->assertSame($a + $b, $result);
        $result = $client->add($a = -20, $b = -10);
        $this->assertSame($a + $b, $result);
    }
}
