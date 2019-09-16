<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Clickhouse;

use ClickHouseDB\Client;
use Hyperf\Contract\ConfigInterface;

class ClickhouseFactory
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function create($pool = 'default'): Client
    {
        $config = $this->config->get('clickhouse.' . $pool, []);
        $settings = $config['settings'] ?? [];

        return new Client($config, $settings);
    }
}
