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
namespace Hyperf\ReactiveX\Example;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcSubject;
use Rx\Subject\ReplaySubject;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;

class WebSocketsController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @var IpcSubject
     */
    private $subject;

    /**
     * @var array
     */
    private $subscriber = [];

    public function __construct(BroadcasterInterface $broadcaster)
    {
        $relaySubject = make(ReplaySubject::class, ['bufferSize' => 5]);
        $this->subject = new IpcSubject($relaySubject, $broadcaster, 1);
    }

    public function onMessage($server, Frame $frame): void
    {
        $this->subject->onNext($frame->data);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $this->subscriber[$fd]->dispose();
    }

    public function onOpen($server, Request $request): void
    {
        $this->subscriber[$request->fd] = $this->subject->subscribe(function ($data) use ($server, $request) {
            if ($server instanceof Response) {
                $server->push($data);
            } else {
                $server->push($request->fd, $data);
            }
        });
    }
}
