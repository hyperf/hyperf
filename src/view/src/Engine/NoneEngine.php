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

namespace Hyperf\View\Engine;

class NoneEngine implements EngineInterface
{
    public function render(string $template, array $data, array $config): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Hyperf</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="jumbotron">
        <h1>Hyperf</h1>
        <p>Hyperf is an extremely performant and flexible PHP CLI framework based on Swoole 4.5+, powered by the
            state-of-the-art coroutine server and a large number of battle-tested components. Aside from the decisive
            benchmark outmatching against PHP-FPM frameworks, Hyperf also distinct itself by its focus on flexibility
            and composability. Hyperf ships with an AOP-enabling dependency injector to ensure components and classes
            are pluggable and meta programmable. All of its core components strictly follow the PSR standards and thus
            can be used in other frameworks.</p>
        <p><a class="btn btn-primary btn-lg" href="https://hyperf.wiki/" role="button">Learn more</a></p>
        <p>This view engine is not available, please use engines below.</p>
        <ul class="list-group">
            <li class="list-group-item"><a href="https://github.com/hyperf/view-engine">hyperf/view-engine</a></li>
            <li class="list-group-item"><a href="https://github.com/duncan3dc/blade">duncan3dc/blade</a></li>
            <li class="list-group-item"><a href="https://github.com/smarty-php/smarty">smarty/smarty</a></li>
            <li class="list-group-item"><a href="https://github.com/twigphp/Twig">twig/twig</a></li>
            <li class="list-group-item"><a href="https://github.com/thephpleague/plates">league/plates</a></li>
            <li class="list-group-item"><a href="https://github.com/sy-records/think-template">sy-records/think-template</a></li>
        </ul>
    </div>
</div>
</body>
</html>
HTML;
    }
}
