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
namespace Hyperf\Metric;

use Hyperf\Contract\Arrayable;

/**
 * @property int $start_time 服务器启动的时间
 * @property int $connection_num 当前连接的数量
 * @property int $accept_count 接受了多少个连接
 * @property int $close_count 关闭的连接数量
 * @property int $worker_num 开启了多少个 worker 进程
 * @property int $idle_worker_num 空闲的 worker 进程数
 * @property int $task_worker_num 开启了多少个 task_worker 进程【v4.5.7 可用】
 * @property int $tasking_num 当前正在排队的任务数
 * @property int $request_count Server 收到的请求次数【只有 onReceive、onMessage、onRequset、onPacket 四种数据请求计算 request_count】
 * @property int $response_count Server 发送的响应次数【只有 onReceive、onMessage、onRequset、onPacket 四种数据请求计算 response_count】
 * @property int $dispatch_count Server 发送到 Worker 的包数量【v4.5.7 可用，仅在 SWOOLE_PROCESS 模式下有效】
 * @property int $worker_request_count 当前 Worker 进程收到的请求次数【worker_request_count 超过 max_request 时工作进程将退出】
 * @property int $worker_dispatch_count master 进程向当前 Worker 进程投递任务的计数，在 master 进程进行 dispatch 时增加计数
 * @property int $task_queue_num 消息队列中的 task 数量【用于 Task】
 * @property int $task_queue_bytes 消息队列的内存占用字节数【用于 Task】
 * @property int $task_idle_worker_num 空闲的 task 进程数量
 * @property int $coroutine_num 当前协程数量【用于 Coroutine】，想获取更多信息参考此节
 */
class CoroutineServerStats implements Arrayable
{
    protected array $stats = [
        'worker_num' => 1,
        'idle_worker_num' => 0,
    ];

    public function __get($name)
    {
        return $this->stats[$name] ?? 0;
    }

    public function __set($name, $value)
    {
        $this->stats[$name] = $value;
    }

    public function toArray(): array
    {
        return $this->stats;
    }
}
