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
namespace Hyperf\Rpc\Contract;

/**
 * This interface ONLY use for retrieve the packer from DI container,
 * Please DONOT implement this interface, should ALWAYS implement the
 * \Hyperf\Contract\PackerInterface interface.
 */
interface PackerInterface extends \Hyperf\Contract\PackerInterface
{
}
