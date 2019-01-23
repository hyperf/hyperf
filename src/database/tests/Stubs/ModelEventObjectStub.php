<?php

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Model;

class ModelEventObjectStub extends Model
{
    protected $dispatchesEvents = [
        'saving' => ModelSavingEventStub::class,
    ];
}