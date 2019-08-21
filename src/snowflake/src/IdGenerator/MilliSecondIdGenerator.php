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

namespace Hyperf\Snowflake\IdGenerator;

use Hyperf\Snowflake\ConfigInterface;
use Hyperf\Snowflake\IdGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

class MilliSecondIdGenerator extends IdGenerator
{
    /**
     * @var int
     */
    protected $beginTimeStamp;

    public function __construct(MetaGeneratorInterface $metaGenerator, ConfigInterface $config, int $beginTimeStamp = self::DEFAULT_SECOND)
    {
        $this->beginTimeStamp = $beginTimeStamp * 1000;
        parent::__construct($metaGenerator, $config);
    }

    public function getBeginTimeStamp(): int
    {
        return $this->beginTimeStamp;
    }
}
