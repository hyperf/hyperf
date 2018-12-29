<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;

class RouteCollectorFactory
{
    public function __invoke()
    {
        $parser = new Std();
        $generator = new DataGenerator();
        /** @var RouteCollector $routeCollector */
        return new RouteCollector($parser, $generator);
    }
}
