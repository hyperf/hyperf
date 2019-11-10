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

namespace Hyperf\Encrypt\Handler;

use Hyperf\Encrypt\Exception\DecryptException;
use Hyperf\Encrypt\Exception\VerifyException;
use Hyperf\Encrypt\SecretKeyInterface;
use Hyperf\Utils\Codec\Json;

class EncryptHandler implements EncryptHandlerInterface
{
    /**
     * @var SecretKeyInterface
     */
    protected $secretKey;

    public function __construct(SecretKeyInterface $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function encrypt($data): string
    {
        openssl_public_encrypt(
            Json::encode($data),
            $encryptData,
            $this->secretKey->getPublicKey(),
            $this->secretKey->getPadding()
        );
        return base64_encode($encryptData);
    }

    public function decrypt(string $encryptData)
    {
        openssl_private_decrypt(
            base64_decode($encryptData),
            $decryptData,
            $this->secretKey->getPrivateKey(),
            $this->secretKey->getPadding()
        );
        if (! $decryptData) {
            throw new DecryptException('Decryption failure');
        }
        return Json::decode($decryptData);
    }

    public function sign($data)
    {
        if (! is_array($data)) {
            $data = [$data];
        }
        ksort($data);
        $body = urldecode(http_build_query($data));
        openssl_sign($body, $sign, $this->secretKey->getPrivateKey());
        return $data + ['sign' => base64_encode($sign)];
    }

    public function verify(string $raw)
    {
        parse_str($raw, $parsedBody);
        if (! is_array($parsedBody) || empty($parsedBody['sign'])) {
            throw new VerifyException('Sign is empty');
        }

        $sign = $parsedBody['sign'];
        unset($parsedBody['sign']);
        ksort($parsedBody);
        $body = urldecode(http_build_query($parsedBody));

        if (! openssl_verify($body, base64_decode($sign), $this->secretKey->getPublicKey()) == 1) {
            throw new VerifyException('Verification failure');
        }
        return $parsedBody;
    }
}
