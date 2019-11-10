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

namespace Hyperf\Encrypt\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Encrypt extends AbstractAnnotation
{
    const BEFORE_VERIFY = 'verify';

    const BEFORE_DECRYPT = 'decrypt';

    const AFTER_SIGN = 'sign';

    const AFTER_ENCRYPT = 'encrypt';

    /**
     * @var string
     */
    public $before;

    /**
     * @var string
     */
    public $after;

    /**
     * @var string
     */
    public $publicKey;

    /**
     * @var string
     */
    public $privateKey;

    /**
     * @var int
     */
    public $padding = OPENSSL_PKCS1_PADDING;
}
