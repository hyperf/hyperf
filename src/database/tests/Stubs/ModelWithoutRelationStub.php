<?php

namespace HyperfTest\Database\Stubs;
use Hyperf\Database\Model\Model;

class ModelWithoutRelationStub extends Model
{
    public $with = ['foo'];

    protected $guarded = [];

    public function getEagerLoads()
    {
        return $this->eagerLoads;
    }
}