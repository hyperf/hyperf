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
namespace Hyperf\MqttServer;


use Hyperf\Contract\OnReceiveInterface;
use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\ConfigInterface;
use Throwable;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Simps\MQTT\Protocol\Types;
use Simps\MQTT\Protocol\V3;
use Simps\MQTT\Tools\Common;
use Simps\MQTT\Message\ConnAck;
use Simps\MQTT\Message\Publish;
use Simps\MQTT\Message\PubAck;
use Simps\MQTT\Message\SubAck;
use Simps\MQTT\Message\UnSubAck;
use Simps\MQTT\Message\PingResp;

class Server implements OnReceiveInterface,MiddlewareInitializerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $receiveCallbacks = [];


    /**
     * @var string
     */
    protected $serverName = 'mqtt';

    public function __construct(ContainerInterface $container,StdoutLoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;

    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;

        $config = $this->container->get(ConfigInterface::class);

        foreach($config->get('server.servers') as $server)
        {
            if($server['name'] === $this->serverName)
            {
                $this->receiveCallbacks =$server['receiveCallbacks'];
            }
        }

    }



    public function OnReceive($server, $fd, $fromId, $data): void
    {
        try {
            // debug
//        Common::printf($data);
            $data = V3::unpack($data);
//            var_dump($data);
            if (is_array($data) && isset($data['type'])) {
                switch ($data['type']) {
                    case Types::CONNECT:
                        [$class, $func] = $this->receiveCallbacks[Types::CONNECT];
                        $obj = new $class();
                        if($arr = $obj->{$func}($server, $fd, $fromId, $data))
                        {
                            $server->send(
                                $fd,
                                (new ConnAck($arr))->setCode(0)
                                    ->setSessionPresent(0)
                            );
                        }

                        // Check protocol_name
//                        if ($data['protocol_name'] != 'MQTT') {
//                            $server->close($fd);
//
//                            return false;
//                        }
//
//                        // Check connection information, etc.
//
//                        $server->send(
//                            $fd,
//                            (new ConnAck())->setCode(0)
//                                ->setSessionPresent(0)
//                        );
                        break;
                    case Types::PINGREQ:
                        [$class, $func] = $this->receiveCallbacks[Types::PINGREQ];
                        $obj = new $class();
                        if($arr = $obj->{$func}($server, $fd, $fromId, $data))
                        {
                            $server->send($fd, (new PingResp($arr)));
                        }

                        break;
                    case Types::DISCONNECT:
                        [$class, $func] = $this->receiveCallbacks[Types::DISCONNECT];
                        $obj = new $class();
                        $obj->{$func}($server, $fd, $fromId, $data);
                        if ($server->exist($fd)) {
                            $server->close($fd);
                        }
                        break;
                    case Types::PUBLISH:
                        // Send to subscribers
//                        var_dump($server->connections);
                        foreach ($server->connections as $sub_fd) {
                            $server->send(
                                $sub_fd,
                                (new Publish())->setTopic($data['topic'])
                                    ->setMessage($data['message'])
                                    ->setDup($data['dup'])
                                    ->setQos($data['qos'])
                                    ->setRetain($data['retain'])
                                    ->setMessageId($data['message_id'] ?? null)
                            );
                        }

                        if ($data['qos'] === 1) {
                            $server->send(
                                $fd,
                                (new PubAck())->setMessageId($data['message_id'] ?? '')
                            );
                        }

                        break;
                    case Types::SUBSCRIBE:
                        $payload = [];
                        foreach ($data['topics'] as $k => $qos) {
                            if (is_numeric($qos) && $qos < 3) {
                                $payload[] = $qos;
                            } else {
                                $payload[] = 0x80;
                            }
                        }
                        $server->send(
                            $fd,
                            (new SubAck())->setMessageId($data['message_id'] ?? '')
                                ->setCodes($payload)
                        );
                        break;
                    case Types::UNSUBSCRIBE:
                        [$class, $func] = $this->receiveCallbacks[Types::PINGREQ];
                        $obj = new $class();
                        $obj->{$func}($server, $fd, $fromId, $data);
                        $server->send(
                            $fd,
                            (new UnSubAck())->setMessageId($data['message_id'] ?? '')
                        );
                        break;
                }
            } else {
                $server->close($fd);
            }
        } catch (\Throwable $e) {
            echo "\033[0;31mError: {$e->getMessage()}\033[0m\r\n";
            $server->close($fd);
        }
    }

    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @return $this
     */
    public function setServerName(string $serverName)
    {
        $this->serverName = $serverName;
        return $this;
    }


}
