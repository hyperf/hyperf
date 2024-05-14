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

namespace Hyperf\DbConnection\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Seeders\Seed;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterSeederPathsListener implements ListenerInterface
{
    public function __construct(protected ConfigInterface $config, protected Seed $seed)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $paths = $this->config->get('seeder.paths', []);

        foreach ($paths as $path) {
            $this->seed->path($path);
        }
    }
}
