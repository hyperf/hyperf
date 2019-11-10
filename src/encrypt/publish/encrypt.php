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

return [
    'padding' => OPENSSL_PKCS1_PADDING,
    'before' => \Hyperf\Encrypt\Annotation\Encrypt::BEFORE_DECRYPT,
    'after' => \Hyperf\Encrypt\Annotation\Encrypt::AFTER_ENCRYPT,
    'publicKey' => BASE_PATH . '/storage/secretKey/rsa_public_key.pub',
    'privateKey' => BASE_PATH . '/storage/secretKey/rsa_private_key.pem',
];
