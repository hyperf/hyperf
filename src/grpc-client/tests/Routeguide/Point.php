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
 * Points are represented as latitude-longitude pairs in the E7 representation
 * (degrees multiplied by 10**7 and rounded to the nearest integer).
 * Latitudes should be in the range +/- 90 degrees and longitude should be in
 * the range +/- 180 degrees (inclusive).
 *
 * Generated from protobuf message <code>routeguide.Point</code>
 */
class Point extends Message
{
    /**
     * Generated from protobuf field <code>int32 latitude = 1;</code>.
     */
    private $latitude = 0;

    /**
     * Generated from protobuf field <code>int32 longitude = 2;</code>.
     */
    private $longitude = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var int $latitude
     * @var int $longitude
     *          }
     */
    public function __construct($data = null)
    {
        RouteGuide::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>int32 latitude = 1;</code>.
     * @return int
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Generated from protobuf field <code>int32 latitude = 1;</code>.
     * @param int $var
     * @return $this
     */
    public function setLatitude($var)
    {
        GPBUtil::checkInt32($var);
        $this->latitude = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 longitude = 2;</code>.
     * @return int
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Generated from protobuf field <code>int32 longitude = 2;</code>.
     * @param int $var
     * @return $this
     */
    public function setLongitude($var)
    {
        GPBUtil::checkInt32($var);
        $this->longitude = $var;

        return $this;
    }
}
