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
namespace Hyperf\ModelCache;

use Hyperf\Database\Model\Builder as ModelBuilder;
use Hyperf\Utils\ApplicationContext;

class Builder extends ModelBuilder
{
    public function delete()
    {
        return $this->deleteCache(function () {
            return parent::delete();
        });
    }

    public function update(array $values)
    {
        return $this->deleteCache(function () use ($values) {
            return parent::update($values);
        });
    }

    protected function deleteCache(\Closure $closure)
    {
        $queryBuilder = clone $this;
        $primaryKey = $this->model->getKeyName();
        $ids = [];
        $models = $queryBuilder->get([$primaryKey]);
        foreach ($models as $model) {
            $ids[] = $model->{$primaryKey};
        }
        if (empty($ids)) {
            return 0;
        }

        $result = $closure();

        $manger = ApplicationContext::getContainer()->get(Manager::class);

        $manger->destroy($ids, get_class($this->model));

        return $result;
    }
}
