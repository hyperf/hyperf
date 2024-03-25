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

namespace Hyperf\Snowflake\MetaGenerator;

use Hyperf\Snowflake\ConfigurationInterface;
use Hyperf\Snowflake\MetaGenerator;

class RandomMilliSecondMetaGenerator extends MetaGenerator
{
    public function __construct(ConfigurationInterface $configuration, int $beginTimestamp)
    {
        parent::__construct($configuration, $beginTimestamp * 1000);
    }

    public function getDataCenterId(): int
    {
        return rand(0, 31);
    }

    public function getWorkerId(): int
    {
        return rand(0, 31);
    }

    public function getTimestamp(): int
    {
        return intval(microtime(true) * 1000);
    }

    public function getNextTimestamp(): int
    {
        $timestamp = $this->getTimestamp();
        while ($timestamp <= $this->lastTimestamp) {
            $timestamp = $this->getTimestamp();
        }

        return $timestamp;
    }
}
