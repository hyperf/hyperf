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
# source: Grpc/grpc.proto

namespace HyperfTest\ResourceGrpc\Stubs\Grpc;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use HyperfTest\ResourceGrpc\Stubs\Grpc\GPBMetadata\Grpc\Grpc;

/**
 * Generated from protobuf message <code>HyperfTest.ResourceGrpc.Stubs.Grpc.AllReply</code>.
 */
class AllReply extends Message
{
    /**
     * Generated from protobuf field <code>string message = 1;</code>.
     */
    private $message = '';

    /**
     * Generated from protobuf field <code>repeated .HyperfTest.ResourceGrpc.Stubs.Grpc.HiUser users = 2;</code>.
     */
    private $users;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var string $message
     * @var \HyperfTest\ResourceGrpc\Stubs\Grpc\HiUser[]|RepeatedField $users
     *                                                                 }
     */
    public function __construct($data = null)
    {
        Grpc::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string message = 1;</code>.
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Generated from protobuf field <code>string message = 1;</code>.
     * @param string $var
     * @return $this
     */
    public function setMessage($var)
    {
        GPBUtil::checkString($var, true);
        $this->message = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>repeated .HyperfTest.ResourceGrpc.Stubs.Grpc.HiUser users = 2;</code>.
     * @return RepeatedField
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Generated from protobuf field <code>repeated .HyperfTest.ResourceGrpc.Stubs.Grpc.HiUser users = 2;</code>.
     * @param \HyperfTest\ResourceGrpc\Stubs\Grpc\HiUser[]|RepeatedField $var
     * @return $this
     */
    public function setUsers($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, GPBType::MESSAGE, HiUser::class);
        $this->users = $arr;

        return $this;
    }
}
