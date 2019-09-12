<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * Date: 2019/9/10
 * Time: 15:38
 * Email: languageusa@163.com
 * Author: Dickens7
 */

namespace Hyperf\Session\Exception;

class InvalidConfigException extends \RuntimeException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}