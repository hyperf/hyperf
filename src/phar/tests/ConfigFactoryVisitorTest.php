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

namespace HyperfTest\Phar;

use Hyperf\Phar\Ast\Ast;
use Hyperf\Phar\Ast\Visitor\RewriteConfigFactoryVisitor;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ConfigFactoryVisitorTest extends TestCase
{
    public function testRewriteConfig()
    {
        $code = BASE_PATH . '/src/config/src/ConfigFactory.php';
        $code = file_get_contents($code);
        $code = (new Ast())->parse($code, [new RewriteConfigFactoryVisitor()]);
        $this->assertSame("<?php

declare (strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\\Config;

use Hyperf\\Collection\\Arr;
use Psr\\Container\\ContainerInterface;
use Symfony\\Component\\Finder\\Finder;
class ConfigFactory
{
    public function __invoke(ContainerInterface \$container)
    {
        \$configPath = BASE_PATH . '/config';
        \$config = \$this->readConfig(\$configPath . '/config.php');
        \$autoloadConfig = \$this->readPaths([\$configPath . '/autoload']);
        \$merged = array_merge_recursive(ProviderConfig::load(), \$config, ...\$autoloadConfig);
        return new Config(\$merged);
    }
    private function readConfig(string \$configPath) : array
    {
        \$config = [];
        if (file_exists(\$configPath) && is_readable(\$configPath)) {
            \$config = (require \$configPath);
        }
        return is_array(\$config) ? \$config : [];
    }
    private function readPaths(array \$paths)
    {
        \$configs = [];
        \$finder = new Finder();
        \$finder->files()->in(\$paths)->name('*.php');
        foreach (\$finder as \$file) {
            \$config = [];
            \$key = implode('.', array_filter([str_replace('/', '.', \$file->getRelativePath()), \$file->getBasename('.php')]));
            \\Hyperf\\Collection\\Arr::set(\$config, \$key, require \$file->getPathname());
            \$configs[] = \$config;
        }
        return \$configs;
    }
}", $code);
    }
}
