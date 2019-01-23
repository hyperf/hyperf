<?php

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Model;

class ModelNonIncrementingStub extends Model
{
    protected $table = 'stub';
    protected $guarded = [];
    public $incrementing = false;
}