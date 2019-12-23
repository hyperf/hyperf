<?php


namespace HyperfTest\Scout\Stub;


use Hyperf\Database\Model\Model;

class ElasticsearchEngineTestModel extends Model
{
    public function getIdAttribute()
    {
        return 1;
    }
    public function searchableAs()
    {
        return 'table';
    }
    public function getKey()
    {
        return '1';
    }
    public function toSearchableArray()
    {
        return ['id' => 1];
    }
}