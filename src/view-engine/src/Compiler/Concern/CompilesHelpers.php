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
namespace Hyperf\ViewEngine\Compiler\Concern;

trait CompilesHelpers
{

/**
* Compile the "d" statements into valid PHP.
*
* @param string $arguments
* @return string
*/
protected function compiled($arguments)
{
return "<?php echo d{$arguments}; ?>";
}

}
