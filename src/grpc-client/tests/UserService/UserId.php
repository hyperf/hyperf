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
# source: user.proto

namespace UserService;

use Google\Protobuf\Internal\GPBUtil;
use Google\Protobuf\Internal\Message;
use GPBMetadata\User;

/**
 * Generated from protobuf message <code>UserService.UserId</code>.
 */
class UserId extends Message
{
    /**
     * Generated from protobuf field <code>uint64 id = 1;</code>.
     */
    protected $id = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var int|string $id
     *                 }
     */
    public function __construct($data = null)
    {
        User::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>uint64 id = 1;</code>.
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generated from protobuf field <code>uint64 id = 1;</code>.
     * @param int|string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkUint64($var);
        $this->id = $var;

        return $this;
    }
}
