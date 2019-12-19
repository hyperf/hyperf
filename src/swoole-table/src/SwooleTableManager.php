<?php
declare(strict_types=1);

/**
 *
 * @author zhenguo.guan
 * @date 2019-12-19 10:20
 */


namespace Hyperf\SwooleTable;

use Swoole\Table;

class SwooleTableManager
{

    protected $container;

    /**
     * 增加SwooleTable
     * @param string $tableName
     * @param Table $table
     * @return bool
     * @author zhenguo.guan
     */
    public function add(string $tableName, Table $table): bool
    {
        if (isset($this->container[$tableName])) {
            return false;
        }
        $this->container[$tableName] = $table;
        return true;
    }

    /**
     * 获取SwooleTable
     * @param string $tableName
     * @return Table|null
     * @author zhenguo.guan
     */
    public function get(string $tableName): ?Table
    {
        return $this->container[$tableName] ?? null;
    }
}