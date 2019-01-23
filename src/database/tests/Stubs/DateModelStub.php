<?php

namespace HyperfTest\Database\Stubs;


class DateModelStub extends ModelStub
{
    public function getDates()
    {
        return ['created_at', 'updated_at'];
    }
}