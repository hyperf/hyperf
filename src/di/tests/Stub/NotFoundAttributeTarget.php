<?php

namespace HyperfTest\Di\Stub;

#[NotExistAttribute]
class NotFoundAttributeTarget
{

    #[NotExistAttribute]
    public $foo;

    #[NotExistAttribute]
    public function foo()
    {

    }

}