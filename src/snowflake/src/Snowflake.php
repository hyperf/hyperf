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

class Snowflake implements IdGeneratorInterface
{
    /**
     * @var MetaGeneratorInterface
     */
    protected $metaGenerator;

    /**
     * @var int
     */
    protected $level;

    /**
     * @var int
     */
    protected $beginSecond;

    public function __construct(MetaGeneratorInterface $metaGenerator, int $level = self::LEVEL_MILLISECOND, int $beginSecond = self::DEFAULT_SECOND)
    {
        $this->metaGenerator = $metaGenerator;
        $this->level = $level;
        $this->beginSecond = $level == self::LEVEL_SECOND ? $beginSecond : $beginSecond * 1000;
    }

    public function generate(?Meta $meta = null): int
    {
        $meta = $this->meta($meta);

        $timestamp = $this->getTimestamp();

        $timestamp = ($timestamp - $this->beginSecond) << $this->getTimestampShift();
        $businessId = $meta->businessId << $this->getBusinessIdShift();
        $dataCenterId = $meta->dataCenterId << $this->getDataCenterShift();
        $machineId = $meta->machineId << $this->getMachineIdShift();

        return $timestamp | $businessId | $dataCenterId | $machineId | $meta->sequence;
    }

    public function degenerate(int $id): Meta
    {
        $timestamp = $id >> $this->getTimestampShift();
        $businessId = $id >> $this->getBusinessIdShift();
        $dataCenterId = $id >> $this->getDataCenterShift();
        $machineId = $id >> $this->getMachineIdShift();

        return (new Meta(
            $timestamp << Meta::BUSINESS_ID_BITS ^ $businessId,
            $businessId << Meta::DATA_CENTER_ID_BITS ^ $dataCenterId,
            $dataCenterId << Meta::MACHINE_ID_BITS ^ $machineId,
            $machineId << Meta::SEQUENCE_BITS ^ $id
        ))->setTimestamp($timestamp + $this->beginSecond);
    }

    protected function getTimestampShift()
    {
        return Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS + Meta::DATA_CENTER_ID_BITS + Meta::BUSINESS_ID_BITS;
    }

    protected function getBusinessIdShift()
    {
        return Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS + Meta::DATA_CENTER_ID_BITS;
    }

    protected function getDataCenterShift()
    {
        return Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS;
    }

    protected function getMachineIdShift()
    {
        return Meta::SEQUENCE_BITS;
    }

    protected function getTimestamp(): int
    {
        if ($this->level == self::LEVEL_SECOND) {
            return time();
        }
        return intval(microtime(true) * 1000);
    }

    protected function meta(?Meta $meta = null): Meta
    {
        if (is_null($meta)) {
            return $this->metaGenerator->generate();
        }

        return $meta;
    }
}
