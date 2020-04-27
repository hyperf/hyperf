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
namespace Hyperf\SocketIOServer\Emitter;

use Hyperf\SocketIOServer\Collector\IORouter;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Packet;
use Hyperf\SocketIOServer\Room\AdapterInterface;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\Utils\ApplicationContext;
use Hyperf\WebSocketServer\Sender;

/**
 * Trait Emitter.
 * @property bool $broadcast
 * @property bool $local
 * @property bool $compress
 */
trait Emitter
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var callable
     */
    protected $addCallback;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var SidProviderInterface
     */
    protected $sidProvider;

    private $fd = -1;

    private $to = [];

    private $broadcast = false;

    private $local = false;

    private $compress = false;

    private $volatile = false;

    private $binary = false;

    public function __get($flag)
    {
        if (in_array($flag, ['broadcast', 'compress', 'binary', 'volatile', 'local'])) {
            $copy = clone $this;
            $copy->{$flag} = true;
            return $copy;
        }
        return $flag;
    }

    public function broadcast(bool $broadcast): self
    {
        $copy = clone $this;
        $copy->broadcast = true;
        return $copy;
    }

    public function compress(bool $compress): self
    {
        $copy = clone $this;
        $copy->compress = $compress;
        return $copy;
    }

    public function volatile(bool $volatile): self
    {
        $copy = clone $this;
        $copy->volatile = $volatile;
        return $copy;
    }

    public function binary(bool $binary): self
    {
        $copy = clone $this;
        $copy->binary = $binary;
        return $copy;
    }

    public function local(bool $local): self
    {
        $copy = clone $this;
        $copy->local = $local;
        return $copy;
    }

    /**
     * @param int|string $room
     */
    public function to($room): self
    {
        $copy = clone $this;
        $copy->to[] = (string) $room;
        return $copy;
    }

    /**
     * @param int|string $room
     */
    public function in($room): self
    {
        return $this->to($room);
    }

    /**
     * @param mixed ...$data
     * @return Future|void
     */
    public function emit(string $event, ...$data)
    {
        if ($this->broadcast || ! empty($this->to)) {
            return $this->adapter->broadcast(
                $this->encode('', $event, $data),
                [
                    'except' => [$this->sidProvider->getSid($this->fd)],
                    'rooms' => $this->to,
                    'flag' => [
                        'compress' => $this->realGet('compress'),
                        'volatile' => $this->realGet('volatile'),
                        'local' => $this->realGet('local'),
                    ],
                ]
            );
        }
        if ($this->realGet('compress')) {
            $wsFlag = SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS;
        } else {
            $wsFlag = SWOOLE_WEBSOCKET_FLAG_FIN;
        }
        return make(Future::class, [
            'fd' => $this->fd,
            'data' => $this->encode('', $event, $data),
            'opcode' => SWOOLE_WEBSOCKET_OPCODE_TEXT,
            'flag' => $wsFlag,
        ]);
    }

    public function getNsp()
    {
        return IORouter::getNamespace(static::class);
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    protected function encode(string $id, $event, $data)
    {
        $encoder = ApplicationContext::getContainer()->get(Encoder::class);
        $packet = Packet::create([
            'type' => Packet::EVENT,
            'nsp' => method_exists($this, 'getNsp') ? $this->getNsp() : '/',
            'id' => $id,
            'data' => array_merge([$event], $data),
        ]);
        return '4' . $encoder->encode($packet);
    }

    private function realGet($flag)
    {
        return $this->{$flag};
    }
}
