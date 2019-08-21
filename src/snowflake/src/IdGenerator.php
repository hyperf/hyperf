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

namespace Hyperf\Snowflake;

abstract class IdGenerator implements IdGeneratorInterface
{
    /**
     * @var MetaGeneratorInterface
     */
    protected $metaGenerator;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(MetaGeneratorInterface $metaGenerator)
    {
        $this->metaGenerator = $metaGenerator;
        $this->config = $metaGenerator->getConfig();
    }

    public function generate(?Meta $meta = null): int
    {
        $meta = $this->meta($meta);

        $interval = $meta->getTimeInterval() << $this->config->getTimeStampShift();
        $dataCenterId = $meta->getDataCenterId() << $this->config->getDataCenterShift();
        $workerId = $meta->getWorkerId() << $this->config->getWorkerIdShift();

        return $interval | $dataCenterId | $workerId | $meta->getSequence();
    }

    public function degenerate(int $id): Meta
    {
        $interval = $id >> $this->config->getTimeStampShift();
        $dataCenterId = $id >> $this->config->getDataCenterShift();
        $workerId = $id >> $this->config->getWorkerIdShift();

        return new Meta(
            $interval << $this->config->getDataCenterBits() ^ $dataCenterId,
            $dataCenterId << $this->config->getWorkerBits() ^ $workerId,
            $workerId << $this->config->getSequenceBits() ^ $id,
            $interval + $this->metaGenerator->getBeginTimeStamp(),
            $this->metaGenerator->getBeginTimeStamp()
        );
    }

    protected function meta(?Meta $meta = null): Meta
    {
        if (is_null($meta)) {
            return $this->metaGenerator->generate();
        }

        return $meta;
    }
}
