<?php

namespace HyperfTest\Database\Stubs;

use Hyperf\Database\Model\Model;

class ModelAppendsStub extends Model
{
    protected $appends = ['is_admin', 'camelCased', 'StudlyCased'];

    public function getIsAdminAttribute()
    {
        return 'admin';
    }

    public function getCamelCasedAttribute()
    {
        return 'camelCased';
    }

    public function getStudlyCasedAttribute()
    {
        return 'StudlyCased';
    }
}