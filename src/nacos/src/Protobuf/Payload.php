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
use Google\Protobuf\Internal\Message;

/**
 * Generated from protobuf message <code>Payload</code>.
 */
class Payload extends Message
{
    /**
     * Generated from protobuf field <code>.Metadata metadata = 2;</code>.
     */
    protected $metadata;

    /**
     * Generated from protobuf field <code>.google.protobuf.Any body = 3;</code>.
     */
    protected $body;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var Metadata $metadata
     * @var Any $body
     *          }
     */
    public function __construct($data = null)
    {
        GPBMetadata\Nacos::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.Metadata metadata = 2;</code>.
     * @return null|Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    public function hasMetadata()
    {
        return isset($this->metadata);
    }

    public function clearMetadata()
    {
        unset($this->metadata);
    }

    /**
     * Generated from protobuf field <code>.Metadata metadata = 2;</code>.
     * @param Metadata $var
     * @return $this
     */
    public function setMetadata($var)
    {
        GPBUtil::checkMessage($var, Metadata::class);
        $this->metadata = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Any body = 3;</code>.
     * @return null|Any
     */
    public function getBody()
    {
        return $this->body;
    }

    public function hasBody()
    {
        return isset($this->body);
    }

    public function clearBody()
    {
        unset($this->body);
    }

    /**
     * Generated from protobuf field <code>.google.protobuf.Any body = 3;</code>.
     * @param Any $var
     * @return $this
     */
    public function setBody($var)
    {
        GPBUtil::checkMessage($var, Any::class);
        $this->body = $var;

        return $this;
    }
}
