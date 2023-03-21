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
# source: nacos.proto

namespace Hyperf\Nacos\Protobuf;

use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>Metadata</code>.
 */
class Metadata extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string type = 3;</code>.
     */
    protected $type = '';

    /**
     * Generated from protobuf field <code>string clientIp = 8;</code>.
     */
    protected $clientIp = '';

    /**
     * Generated from protobuf field <code>map<string, string> headers = 7;</code>.
     */
    private $headers;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     *     @var string $type
     *     @var string $clientIp
     *     @var array|\Google\Protobuf\Internal\MapField $headers
     * }
     */
    public function __construct($data = null)
    {
        GPBMetadata\Nacos::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string type = 3;</code>.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Generated from protobuf field <code>string type = 3;</code>.
     * @param string $var
     * @return $this
     */
    public function setType($var)
    {
        GPBUtil::checkString($var, true);
        $this->type = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>string clientIp = 8;</code>.
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * Generated from protobuf field <code>string clientIp = 8;</code>.
     * @param string $var
     * @return $this
     */
    public function setClientIp($var)
    {
        GPBUtil::checkString($var, true);
        $this->clientIp = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>map<string, string> headers = 7;</code>.
     * @return \Google\Protobuf\Internal\MapField
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Generated from protobuf field <code>map<string, string> headers = 7;</code>.
     * @param array|\Google\Protobuf\Internal\MapField $var
     * @return $this
     */
    public function setHeaders($var)
    {
        $arr = GPBUtil::checkMapField($var, \Google\Protobuf\Internal\GPBType::STRING, \Google\Protobuf\Internal\GPBType::STRING);
        $this->headers = $arr;

        return $this;
    }
}
