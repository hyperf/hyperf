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
namespace Hyperf\SocketIOServer\Emitter;

use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\Parser\Engine;
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
    use Flagger;

    protected ?AdapterInterface $adapter = null;

    /**
     * @var callable
     */
    protected $addCallback;

    protected ?Sender $sender = null;

    protected ?SidProviderInterface $sidProvider = null;

    private int $fd = -1;

    private array $to = [];

    private bool $broadcast = false;

    private bool $local = false;

    private bool $compress = false;

    private bool $volatile = false;

    private bool $binary = false;

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
                        'compress' => $this->compress,
                        'volatile' => $this->volatile,
                        'local' => $this->local,
                    ],
                ]
            );
        }

        return make(Future::class, [
            'fd' => $this->fd,
            'event' => $event,
            'data' => $data,
            'encode' => function ($i, $event, $data) {
                return $this->encode($i, $event, $data);
            },
            'opcode' => SWOOLE_WEBSOCKET_OPCODE_TEXT,
            'flag' => $this->guessFlags($this->compress),
        ]);
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    protected function encode(string $id, $event, $data)
    {
        $encoder = ApplicationContext::getContainer()->get(Encoder::class);
        $packet = Packet::create([
            'type' => Packet::EVENT,
            'nsp' => method_exists($this, 'getNamespace') ? $this->getNamespace() : '/',
            'id' => $id,
            'data' => array_merge([$event], $data),
        ]);
        return Engine::MESSAGE . $encoder->encode($packet);
    }
}
