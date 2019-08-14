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

class SnowFlake implements IdGeneratorInterface
{
    /**
     * @var MetaGeneratorInterface
     */
    protected $metaGenerator;

    /**
     * @var int
     */
    protected $level;

    public function __construct(MetaGeneratorInterface $metaGenerator, int $level = self::LEVEL_MILLISECOND)
    {
        $this->metaGenerator = $metaGenerator;
        $this->level = $level;
    }

    public function generate(?Meta $meta = null): int
    {
        $meta = $this->meta($meta);

        $timestamp = $this->getTimestamp();

        $t = ($timestamp - $meta->beginTimeStamp) << (Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS + Meta::DATA_CENTER_ID_BITS + Meta::BUSINESS_ID_BITS);
        $b = $meta->businessId << (Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS + Meta::DATA_CENTER_ID_BITS);
        $dc = $meta->dataCenterId << (Meta::SEQUENCE_BITS + Meta::MACHINE_ID_BITS);
        $worker = $meta->machineId << Meta::SEQUENCE_BITS;

        return $t | $b | $dc | $worker | $meta->sequence;
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
