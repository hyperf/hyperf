<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\System;

$result = new Channel(1);
$o = Observable::fromCoroutine(function () {
    System::sleep(0.002);
    return 1;
}, function () {
    System::sleep(0.001);
    return 2;
});
$o->take(1)->subscribe(
    function ($x) use ($result) {
        $result->push($x);
    }
);
echo $result->pop(); //2;
