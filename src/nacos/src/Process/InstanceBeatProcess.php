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
namespace Hyperf\Nacos\Process;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Nacos\Lib\NacosInstance;
use Hyperf\Nacos\ThisInstance;
use Hyperf\Nacos\ThisService;
use Hyperf\Process\AbstractProcess;

class InstanceBeatProcess extends AbstractProcess
{
    public $name = 'nacos-beat';

    public function handle(): void
    {
        /** @var ThisInstance $instance */
        $instance = make(ThisInstance::class);
        /** @var NacosInstance $nacos_instance */
        $nacos_instance = make(NacosInstance::class);
        $service = make(ThisService::class);

        $logger = $this->container->get(LoggerFactory::class)->get('nacos');
        while (true) {
            sleep(config('nacos.client.beatInterval', 5));
            $send = $nacos_instance->beat($service, $instance);
            if ($send) {
                $logger->info('nacos send beat success!', compact('instance'));
            } else {
                $logger->error('nacos send beat fail}', compact('instance'));
            }
        }
    }

    public function isEnable($server): bool
    {
        return config('nacos.client.beatEnable', false);
    }
}
