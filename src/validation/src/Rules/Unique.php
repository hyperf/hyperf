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
namespace Hyperf\Validation\Rules;

use Hyperf\Database\Model\Model;

class Unique
{
    use DatabaseRule;

    /**
     * The ID that should be ignored.
     *
     * @var mixed
     */
    protected $ignore;

    /**
     * The name of the ID column.
     *
     * @var string
     */
    protected $idColumn = 'id';

    /**
     * Convert the rule to a validation string.
     */
    public function __toString(): string
    {
        return rtrim(sprintf(
            'unique:%s,%s,%s,%s,%s',
            $this->table,
            $this->column,
            $this->ignore ? '"' . addslashes((string) $this->ignore) . '"' : 'NULL',
            $this->idColumn,
            $this->formatWheres()
        ), ',');
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @param mixed $id
     * @return $this
     */
    public function ignore($id, ?string $idColumn = null)
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @param \Hyperf\Database\Model\Model $model
     * @return $this
     */
    public function ignoreModel($model, ?string $idColumn = null)
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }
}
