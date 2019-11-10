<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Encrypt;

class SecretKey implements SecretKeyInterface
{
    /**
     * @var int
     */
    protected $padding;

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var string
     */
    protected $privateKey;

    public function __construct($padding, $publicKey, $privateKey)
    {
        $this->padding = $padding;
        $this->publicKey = $this->getPath($publicKey);
        $this->privateKey = $this->getPath($privateKey);
    }

    public function getPadding()
    {
        return $this->padding;
    }

    public function getPublicKey()
    {
        return openssl_pkey_get_public(file_get_contents($this->publicKey));
    }

    public function getPrivateKey()
    {
        return openssl_pkey_get_private(file_get_contents($this->privateKey));
    }

    protected function getPath($key)
    {
        if (is_string($key)) {
            return $key;
        }
        if (is_callable($key)) {
            return call($key);
        }
        return null;
    }
}
