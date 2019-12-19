<?php
declare(strict_types=1);

/**
 *
 * @author zhenguo.guan
 * @date 2019-12-19 10:33
 */


namespace Hyperf\SwooleTable;


use Swoole\Table;

interface SwooleTableInterface
{
    public function create(): Table;
}