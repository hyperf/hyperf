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
# source: route_guide.proto

namespace Routeguide;

use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\Message;
use GPBMetadata\RouteGuide;

/**
 * A latitude-longitude rectangle, represented as two diagonally opposite
 * points "lo" and "hi".
 *
 * Generated from protobuf message <code>routeguide.Rectangle</code>
 */
class Rectangle extends Message
{
    /**
     * One corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point lo = 1;</code>
     */
    private $lo;

    /**
     * The other corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point hi = 2;</code>
     */
    private $hi;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var Point $lo
     *            One corner of the rectangle
     * @var Point $hi
     *            The other corner of the rectangle.
     *            }
     */
    public function __construct($data = null)
    {
        RouteGuide::initOnce();
        parent::__construct($data);
    }

    /**
     * One corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point lo = 1;</code>
     * @return Point
     */
    public function getLo()
    {
        return $this->lo;
    }

    /**
     * One corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point lo = 1;</code>
     * @param Point $var
     * @return $this
     */
    public function setLo($var)
    {
        GPBUtil::checkMessage($var, Point::class);
        $this->lo = $var;

        return $this;
    }

    /**
     * The other corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point hi = 2;</code>
     * @return Point
     */
    public function getHi()
    {
        return $this->hi;
    }

    /**
     * The other corner of the rectangle.
     *
     * Generated from protobuf field <code>.routeguide.Point hi = 2;</code>
     * @param Point $var
     * @return $this
     */
    public function setHi($var)
    {
        GPBUtil::checkMessage($var, Point::class);
        $this->hi = $var;

        return $this;
    }
}
