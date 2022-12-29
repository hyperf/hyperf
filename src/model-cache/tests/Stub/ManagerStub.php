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
namespace HyperfTest\ModelCache\Stub;

use DateInterval;
use Hyperf\Database\Model\Model;
use Hyperf\ModelCache\Handler\HandlerInterface;

class ManagerStub extends \Hyperf\ModelCache\Manager
{
    public function getCacheTTL(Model $instance, HandlerInterface $handler): DateInterval|int
    {
        return parent::getCacheTTL($instance, $handler);
    }
}
