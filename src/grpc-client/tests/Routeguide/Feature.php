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
 * A feature names something at a given point.
 * If a feature could not be named, the name is empty.
 *
 * Generated from protobuf message <code>routeguide.Feature</code>
 */
class Feature extends Message
{
    /**
     * The name of the feature.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     */
    private $name = '';

    /**
     * The point where the feature is detected.
     *
     * Generated from protobuf field <code>.routeguide.Point location = 2;</code>
     */
    private $location;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var string $name
     *             The name of the feature
     * @var Point $location
     *            The point where the feature is detected.
     *            }
     */
    public function __construct($data = null)
    {
        RouteGuide::initOnce();
        parent::__construct($data);
    }

    /**
     * The name of the feature.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The name of the feature.
     *
     * Generated from protobuf field <code>string name = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, true);
        $this->name = $var;

        return $this;
    }

    /**
     * The point where the feature is detected.
     *
     * Generated from protobuf field <code>.routeguide.Point location = 2;</code>
     * @return Point
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * The point where the feature is detected.
     *
     * Generated from protobuf field <code>.routeguide.Point location = 2;</code>
     * @param Point $var
     * @return $this
     */
    public function setLocation($var)
    {
        GPBUtil::checkMessage($var, Point::class);
        $this->location = $var;

        return $this;
    }
}
