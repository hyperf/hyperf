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

class Column
{
    protected $name;

    protected $position;

    protected $default;

    protected $type;

    protected $isNull;

    protected $originData;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->drfault;
    }

    /**
     * @param mixed $default
     * @return $this
     */
    public function setDefault($default)
    {
        $this->drfault = $default;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsNull()
    {
        return $this->isNull;
    }

    /**
     * @param mixed $isNull
     * @return $this
     */
    public function setIsNull($isNull)
    {
        $this->isNull = $isNull;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginData()
    {
        return $this->originData;
    }

    /**
     * @param mixed $originData
     * @return $this
     */
    public function setOriginData($originData)
    {
        $this->originData = $originData;
        return $this;
    }
}
