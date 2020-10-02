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
namespace HyperfTest\Logger\Stub;

use Monolog\Processor\ProcessorInterface;

class FooProcessor implements ProcessorInterface
{
    protected $repeat;

    public function __construct(int $repeat)
    {
        $this->repeat = 2;
    }

    public function __invoke(array $records)
    {
        $records['message'] = str_repeat($records['message'], $this->repeat);
        return $records;
    }
}
