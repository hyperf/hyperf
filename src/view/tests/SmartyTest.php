<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\View;

use Hyperf\View\Engine\SmartyEngine;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
class SmartyTest extends TestCase
{
    public function testRender()
    {
        $config = [
            'view_path' => __DIR__ . '/tpl',
            'cache_path' => __DIR__ . '/runtime',
        ];

        $engine = new SmartyEngine();
        $res = $engine->render('index.tpl', ['name' => 'Hyperf'], $config);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using smarty template now.
</body>
</html>', $res);
    }
}
