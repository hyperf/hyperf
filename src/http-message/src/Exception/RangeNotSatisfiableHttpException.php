<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpMessage\Exception;

use yii\web\HttpException;

class RangeNotSatisfiableHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous the previous exception used for the exception chaining
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(416, $message, $code, $previous);
    }
}
