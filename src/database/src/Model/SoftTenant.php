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

use Exception;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Concerns\HasGlobalScopes;
use Hyperf\DbConnection\Model\Model;

/**
 * 使用方法：一般你的orm会extends一个Model，在Model文件中use即可（如你只有部分orm需要，则不要在Model中use，只需要在指定orm中use即可。默认字段名为“tenant_id”，如要修改可在orm中添加成员变量“tenant”即可。）
 * @method static Model tenant(array|string|null $tenantId) 注意如果为null代表不增加租户限制条件。
 */
trait TenantTrait
{
    use HasGlobalScopes;

    public static function bootTenantTrait(): void
    {
        static::addGlobalScope(new TenantScope()); // 全局作用域
    }

    /**
     * 添加一个链式tenant方法（注：这里操作的方式是全局，如修改代码请注意）
     */
    public static function scopeTenant(Builder $builder, array|string|null $_tenantId): Builder
    {
        /*为null代表删除全局租户条件*/
        if ($_tenantId === null) {
            return $builder->withoutGlobalScope(TenantScope::class);
        }

        $tenantId = is_array($_tenantId) ? $_tenantId : [$_tenantId];

        return $builder->withoutGlobalScope(TenantScope::class)->withGlobalScope(TenantScope::class, function (Builder $builder) use ($tenantId) {
            /** @noinspection PhpUndefinedMethodInspection */
            $builder->whereIn($builder->getModel()->getQualifiedTenantIdColumn(), $tenantId);
        });
    }

    /**
     * 获取带数据表的租户字段。
     *
     * @return string
     */
    public function getQualifiedTenantIdColumn(): string
    {
        $column = 'tenant_id';
        if (property_exists($this, 'tenant')) {
            $column = $this->tenant;
        }
        return $column;
    }

    /**
     * 获取租户id的值
     *
     * @return array|null
     * @throws Exception
     */
    public function getTenantIdFun(): array|null
    {
        $column = 'tenantIdCallback';
        if (property_exists($this, $column)) {
            return $this->{$this->{$column}}();
        }
        if ( ! method_exists($this, 'getTenantIdVal')) {
            throw new Exception('请在模型引用处手动实现getTenantIdVal方法，自行组装业务逻辑，也可通过成员变量tenantIdCallback来指定方法名。');
        }
        return $this->getTenantIdVal();
    }
}
