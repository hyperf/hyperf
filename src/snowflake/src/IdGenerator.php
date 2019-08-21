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

    public function __construct(MetaGeneratorInterface $metaGenerator, ConfigInterface $config)
    {
        $this->metaGenerator = $metaGenerator;
        $this->config = $config;
    }

    abstract public function getBeginTimeStamp(): int;

    public function generate(?Meta $meta = null): int
    {
        $meta = $this->meta($meta);

        $timestamp = ($meta->timestamp - $this->getBeginTimeStamp()) << $this->config->getTimeStampShift();
        $dataCenterId = $meta->dataCenterId << $this->config->getDataCenterShift();
        $workerId = $meta->workerId << $this->config->getWorkerIdShift();

        return $timestamp | $dataCenterId | $workerId | $meta->sequence;
    }

    public function degenerate(int $id): Meta
    {
        $timestamp = $id >> $this->config->getTimeStampShift();
        $dataCenterId = $id >> $this->config->getDataCenterShift();
        $workerId = $id >> $this->config->getWorkerIdShift();

        return new Meta(
            $timestamp << $this->config->getDataCenterBits() ^ $dataCenterId,
            $dataCenterId << $this->config->getWorkerBits() ^ $workerId,
            $workerId << $this->config->getSequenceBits() ^ $id,
            $timestamp + $this->getBeginTimeStamp()
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
