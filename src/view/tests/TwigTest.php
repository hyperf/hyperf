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

use Hyperf\View\Engine\TwigEngine;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class TwigTest extends TestCase
{
    public function testRender()
    {
        $config = [
            'view_path' => __DIR__ . '/tpl',
            'cache_path' => __DIR__ . '/runtime',
        ];

        $engine = new TwigEngine();
        $res = $engine->render('index.twig', ['name' => 'Hyperf'], $config);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using twig template now.
</body>
</html>', $res);
    }

    public function testConfig()
    {
        $config = [
            'view_path' => __DIR__ . '/tpl',
            'cache_path' => __DIR__ . '/runtime',
            'template_suffix' => '.twig',
        ];

        $engine = new TwigEngine();
        $res = $engine->render('index', ['name' => 'Hyperf'], $config);

        $this->assertEquals('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, Hyperf. You are using twig template now.
</body>
</html>', $res);
    }
}
