<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nsq;

use Closure;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nsq\Exception\SocketSendException;
use Hyperf\Nsq\Pool\NsqConnection;
use Hyperf\Nsq\Pool\NsqPoolFactory;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Socket;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Nsq\Batch;
class Nsq
{
    /**
     * @var \Swoole\Coroutine\Socket
     */
    protected $socket;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Pool\NsqPool
     */
    protected $pool;

    /**
     * @var MessageBuilder
     */
    protected $builder;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Hyperf\Nsq\Batch
     */
    private $batch;

    /**
     * @var nsqIpList
     */
    private $nsqIpList;
    public function __construct(ContainerInterface $container, string $pool = 'default')
    {
        $this->container = $container;
        $this->builder = $container->get(MessageBuilder::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->cache = $container->get(CacheInterface::class);
        $this->config = $container->get(ConfigInterface::class);
        $this->batch = $container->get(Batch::class);


        $nsqlookup=$this->config->get("nsq")['nsqlookup'];
        if(!$nsqlookup['debug']){
            $this->nsqIpList=$this->cache->get("producerNsqIpList");
            if(empty($this->nsqIpList)){
                $this->nsqIpList=$this->batch->getNsqIpList($nsqlookup);
            }else{
                $this->nsqIpList=json_decode($this->nsqIpList,true);
            }
            //reset config
            $nsqConfig=$this->nsqIpList;
            $nsqConfig['nsqlookup']=$nsqlookup;
            $this->config->set("nsq",$nsqConfig);
            $first_key = key($this->nsqIpList);
            $this->pool = $container->get(NsqPoolFactory::class)->getPool($first_key);
            //delete the pool
            unset($this->nsqIpList[$first_key]);
            $this->resetIpList($this->nsqIpList);
        }else{
            $this->pool = $container->get(NsqPoolFactory::class)->getPool($pool);
        }

    }


    public function batchPublish(string $topic, $message, float $deferTime = 0.0): bool
    {
        try {
            return $this->publish($topic,$message,$deferTime);
        } catch (\Throwable $throwable) {
            $first_key = key($this->nsqIpList);
            $this->pool = $this->container->get(NsqPoolFactory::class)->getPool($first_key);
            return $this->publishDefer($topic,$message);
        }
    }

    /**reset poll
     * @param $nsqIpList
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function resetIpList($nsqIpList)
    {
        if (count($nsqIpList) > 0) {
            $this->cache->set('producerNsqIpList', json_encode($nsqIpList));
        } else {
            $this->cache->set('producerNsqIpList', '');
        }
    }


    /** try again
     * @param $topic
     * @param $messages
     * @param int $deferTime
     * @return bool
     */
    public function publishDefer( $topic, $messages, $deferTime = 0.1)
    {
        try {
            return $this->publish($topic, $messages, $deferTime);
        } catch (\Throwable $throwable) {
            $this->logger->error('send errorMessages:' . $throwable->getMessage());
        }
        return true;
    }


    public function publish(string $topic, $message, float $deferTime = 0.0): bool
    {
        if (is_array($message)) {
            if ($deferTime > 0) {
                foreach ($message as $value) {
                    $this->sendDPub($topic, $value, $deferTime);
                }
                return true;
            }

            return $this->sendMPub($topic, $message);
        }

        if ($deferTime > 0) {
            return $this->sendDPub($topic, $message, $deferTime);
        }

        return $this->sendPub($topic, $message);
    }

    public function subscribe(string $topic, string $channel, callable $callback): void
    {
        $this->call(function (Socket $socket) use ($topic, $channel,$callback) {
            $this->sendSub($socket, $topic, $channel);
            while ($this->sendRdy($socket)) {
                $reader = new Subscriber($socket);
                $reader->recv();

                if ($reader->isMessage()) {
                    if ($reader->isHeartbeat()) {
                        $socket->sendAll($this->builder->buildNop());
                    } else {
                        $message = $reader->getMessage();
                        $result = null;
                        try {
                            $result = $callback($message);
                        } catch (\Throwable $throwable) {
                            $result = Result::DROP;
                            $this->logger->error('Subscribe failed, ' . (string) $throwable);
                        }

                        if ($result === Result::REQUEUE) {
                            $socket->sendAll($this->builder->buildTouch($message->getMessageId()));
                            $socket->sendAll($this->builder->buildReq($message->getMessageId()));
                            continue;
                        }

                        $socket->sendAll($this->builder->buildFin($message->getMessageId()));
                    }
                }
            }
        });
    }

    protected function sendMPub(string $topic, array $messages): bool
    {
        $payload = $this->builder->buildMPub($topic, $messages);
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    protected function sendPub(string $topic, string $message): bool
    {
        $payload = $this->builder->buildPub($topic, $message);
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    protected function sendDPub(string $topic, string $message, float $deferTime = 0.0): bool
    {
        $payload = $this->builder->buildDPub($topic, $message, intval($deferTime * 1000));
        return $this->call(function (Socket $socket) use ($payload) {
            if ($socket->send($payload) === false) {
                throw new ConnectionException('Payload send failed, the errorCode is ' . $socket->errCode);
            }
            return true;
        });
    }

    protected function call(Closure $closure)
    {
        /** @var NsqConnection $connection */
        $connection = $this->pool->get();
        try {
            return $connection->call($closure);
        } catch (\Throwable $throwable) {
            $connection->close();
            throw $throwable;
        } finally {
            $connection->release();
        }
    }

    protected function sendSub(Socket $socket, string $topic, string $channel): void
    {
        $result = $socket->sendAll($this->builder->buildSub($topic, $channel));
        if ($result === false) {
            throw new SocketSendException('SUB send failed, the errorCode is ' . $socket->errCode);
        }
        $socket->recv();
    }

    protected function sendRdy(Socket $socket)
    {
        $result = $socket->sendAll($this->builder->buildRdy(1));
        if ($result === false) {
            throw new SocketSendException('RDY send failed, the errorCode is ' . $socket->errCode);
        }

        return $result;
    }
}
