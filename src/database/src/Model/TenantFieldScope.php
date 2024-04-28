<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  wfuren@qq.com
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Model;

class TenantFieldScope implements Scope
{
    public function apply(Builder $builder, Model $model): Builder
    {
        /**
         * @var TenantTrait $model 这里的class类其实就是调用的Dao层模型。
         */
        /** @noinspection PhpUnhandledExceptionInspection */
        $tenantId = $model->getTenantIdFun();
        if ($model->getQualifiedTenantIdColumn() === null || $tenantId === null) {
            return $builder;
        }
        return $builder->whereIn($model->getQualifiedTenantIdColumn(), $tenantId);
    }
}
