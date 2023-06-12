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
namespace Hyperf\Validation\Contract;

use Hyperf\Contract\ValidatorInterface;

interface ValidatorAwareRule
{
    /**
     * Set the current validator.
     *
     * @param  \Hyperf\Contract\ValidatorInterface  $validator
     */
    public function setValidator(ValidatorInterface $validator);
}
