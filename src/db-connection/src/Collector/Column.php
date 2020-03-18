<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Collector;

use Hyperf\Utils\Str;

class Column
{
    const NULLABLE = 'YES';

    const NOT_NULLABLE = 'NO';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $isNull;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param $data = [
     *     'COLUMN_NAME' => '',
     *     'ORDINAL_POSITION' => 0,
     *     'COLUMN_DEFAULT' => 0,
     *     'DATA_TYPE' => '',
     *     'IS_NULLABLE' => 'YES',
     * ]
     */
    public function __construct(array $data)
    {
        $formatted = $this->format($data);

        $this->name = $formatted['column_name'];
        $this->position = $formatted['ordinal_position'];
        $this->default = $formatted['column_default'];
    }

    protected function format(array $data)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[Str::lower($key)] = $value;
        }

        return $result;
    }
}
