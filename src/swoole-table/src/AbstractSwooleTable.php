<?php
declare(strict_types=1);

/**
 *
 * @author zhenguo.guan
 * @date 2019-12-19 10:39
 */


namespace Hyperf\SwooleTable;


use Swoole\Table;

abstract class AbstractSwooleTable implements SwooleTableInterface
{

    /**
     * 创建SwooleTable
     * @return Table
     * @author zhenguo.guan
     */
    public function create(): Table
    {
        $table = new Table($this->maxSize(), $this->conflictSize());
        $defines = $this->define();
        foreach ($defines as $define) {
            $table->column(...$define);
        }
        $table->create();
        return $table;
    }

    /**
     * 获取表结构
     * @return array 二维数组， 每行数据为一个字段的定义, 结构为[name, type, size]
     * @author zhenguo.guan
     */
    abstract public function define(): array;

    /**
     * SwooleTable可容纳最大记录数
     * @return int
     * @author zhenguo.guan
     */
    abstract public function maxSize(): int;

    /**
     * 预留20%作为hash冲突
     * @return float
     * @author zhenguo.guan
     */
    public function conflictSize(): float
    {
        return 0.2;
    }
}