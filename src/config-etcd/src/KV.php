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
namespace Hyperf\ConfigEtcd;

class KV
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $createRevision;

    /**
     * @var string
     */
    public $modRevision;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $value;

    public function __construct($data)
    {
        $this->key = $data['key'] ?? null;
        $this->createRevision = $data['create_revision'] ?? null;
        $this->modRevision = $data['create_revision'] ?? null;
        $this->version = $data['create_revision'] ?? null;
        $this->value = $data['value'] ?? null;
    }

    public function isValid()
    {
        return isset($this->value, $this->key);
    }
}
