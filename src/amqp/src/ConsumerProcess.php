<?php

namespace Hyperf\Amqp;


use Hyperf\Process\Process;
use Hyperf\Utils\Str;

class ConsumerProcess extends Process
{

    public function handle(): void
    {
        $try = 5;
        for ($k = 0; $k < $try; $k++) {
            try {
                $consumer = $this->container->get(Consumer::class);
                $consumer->consume(new DemoConsumer());
            } catch (\Throwable $e) {
                if (Str::contains($e->getMessage(), [
                    'stream_socket_client(): unable to connect to',
                ])) {
                    printf('Cannot connect to AMQP Server due to [%s].' . PHP_EOL, $e->getMessage());
                    if ($k >= 4) {
                        printf('Process[%s] sleeped.' . PHP_EOL, static::class);
                        sleep(3650 * 24 * 3600);
                    }
                }
            }
        }
    }

}