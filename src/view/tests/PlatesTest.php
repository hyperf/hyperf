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

use Hyperf\View\Engine\PlatesEngine;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class PlatesTest extends TestCase
{
    public function testRender()
    {
        $config = [
            'view_path' => __DIR__ . '/tpl',
            'cache_path' => __DIR__ . '/runtime',
            'file_extension' => 'plates',
        ];

        $engine = new PlatesEngine();
        $res = $engine->render('index', ['name' => 'Hyperf'], $config);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using plates template now.
</body>
</html>', $res);
    }
}
