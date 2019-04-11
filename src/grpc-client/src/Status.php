<?php

namespace Hyperf\GrpcClient;


final class Status
{

    public const CLOSE_KEYWORD = '>>>SWOOLE|CLOSE<<<';

    public const WAIT_PENDDING = 0;

    public const WAIT_FOR_ALL = 1;

    public const WAIT_CLOSE = 2;

    public const WAIT_CLOSE_FORCE = 3;

}