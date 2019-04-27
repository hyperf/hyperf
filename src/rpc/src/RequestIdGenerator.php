<?php

namespace Hyperf\Rpc;


class RequestIdGenerator
{

    public function generate(): int
    {
        $us = strstr(microtime(), ' ', true);
        return intval(strval($us * 1000 * 1000) . rand(100, 999));
    }

}