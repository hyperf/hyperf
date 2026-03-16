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

namespace Hyperf\Snowflake;

abstract class IdGenerator implements IdGeneratorInterface
{
    protected ConfigurationInterface $config;

    public function __construct(protected MetaGeneratorInterface $metaGenerator)
    {
        $this->config = $metaGenerator->getConfiguration();
    }

    public function generate(?Meta $meta = null): int
    {
        $meta = $this->meta($meta);

        $interval = $meta->getTimeInterval() << $this->config->getTimestampLeftShift();
        $dataCenterId = $meta->getDataCenterId() << $this->config->getDataCenterIdShift();
        $workerId = $meta->getWorkerId() << $this->config->getWorkerIdShift();

        return $interval | $dataCenterId | $workerId | $meta->getSequence();
    }

    public function degenerate(int $id): Meta
    {
        $interval = $id >> $this->config->getTimestampLeftShift();
        $dataCenterId = $id >> $this->config->getDataCenterIdShift();
        $workerId = $id >> $this->config->getWorkerIdShift();

        return new Meta(
            $interval << $this->config->getDataCenterIdBits() ^ $dataCenterId,
            $dataCenterId << $this->config->getWorkerIdBits() ^ $workerId,
            $workerId << $this->config->getSequenceBits() ^ $id,
            $interval + $this->metaGenerator->getBeginTimestamp(),
            $this->metaGenerator->getBeginTimestamp()
        );
    }

    public function getMetaGenerator(): MetaGeneratorInterface
    {
        return $this->metaGenerator;
    }

    protected function meta(?Meta $meta = null): Meta
    {
        if (is_null($meta)) {
            return $this->metaGenerator->generate();
        }

        return $meta;
    }
}
