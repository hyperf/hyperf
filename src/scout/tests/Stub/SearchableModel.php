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
namespace HyperfTest\Scout\Stub;

use Hyperf\Database\Model\Model;
use Hyperf\Scout\Searchable;

class SearchableModel extends Model
{
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id'];

    /**
     * @var \Closure
     */
    protected $queryCallback;

    public function searchableAs()
    {
        return 'table';
    }

    public function scoutMetadata()
    {
        return [];
    }

    public function setQueryCallback(\Closure $closure)
    {
        $this->queryCallback = $closure;
    }

    public function newQuery()
    {
        if ($this->queryCallback) {
            return $this->queryCallback->__invoke($this);
        }
        return parent::newQuery();
    }
}
