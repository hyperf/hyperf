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

namespace HyperfTest\GrpcClient\Stub;

use Hyperf\GrpcClient\BaseClient;
use Routeguide\Feature;
use Routeguide\Point;
use Routeguide\RouteNote;
use Routeguide\RouteSummary;

class RouteGuideClient extends BaseClient
{
    public function getFeature(Point $point)
    {
        return $this->_simpleRequest(
            '/routeguide.RouteGuide/GetFeature',
            $point,
            [Feature::class, 'decode']
        );
    }

    public function listFeatures()
    {
        return $this->_serverStreamRequest(
            '/routeguide.RouteGuide/ListFeatures',
            [Feature::class, 'decode']
        );
    }

    public function recordRoute()
    {
        return $this->_clientStreamRequest(
            '/routeguide.RouteGuide/RecordRoute',
            [RouteSummary::class, 'decode']
        );
    }

    public function routeChat()
    {
        return $this->_bidiRequest(
            '/routeguide.RouteGuide/RouteChat',
            [RouteNote::class, 'decode']
        );
    }
}
