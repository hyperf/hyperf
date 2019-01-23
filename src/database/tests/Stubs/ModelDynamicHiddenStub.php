<?php

namespace HyperfTest\Database\Stubs;


use Hyperf\Database\Model\Model;

class ModelDynamicHiddenStub extends Model
{
    protected $table = 'stub';
    protected $guarded = [];

    public function getHidden()
    {
        return ['age', 'id'];
    }
}