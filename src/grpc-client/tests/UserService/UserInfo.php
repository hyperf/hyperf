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
 * Generated from protobuf message <code>UserService.UserInfo</code>.
 */
class UserInfo extends Message
{
    /**
     * Generated from protobuf field <code>uint64 id = 1;</code>.
     */
    protected $id = 0;

    /**
     * Generated from protobuf field <code>string name = 2;</code>.
     */
    protected $name = '';

    /**
     * Generated from protobuf field <code>uint32 gender = 3;</code>.
     */
    protected $gender = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *                    Optional. Data for populating the Message object.
     *
     * @var int|string $id
     * @var string $name
     * @var int $gender
     *          }
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

    /**
     * Generated from protobuf field <code>string name = 2;</code>.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Generated from protobuf field <code>string name = 2;</code>.
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
     * Generated from protobuf field <code>uint32 gender = 3;</code>.
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Generated from protobuf field <code>uint32 gender = 3;</code>.
     * @param int $var
     * @return $this
     */
    public function setGender($var)
    {
        GPBUtil::checkUint32($var);
        $this->gender = $var;

        return $this;
    }
}
