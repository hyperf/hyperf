<?php

namespace HyperfTest\Database\Stubs;


use Hyperf\Database\Model\Model;

class ModelDynamicVisibleStub extends Model
{
    protected $table = 'stub';
    protected $guarded = [];

    public function getVisible()
    {
        return ['name', 'id'];
    }
}