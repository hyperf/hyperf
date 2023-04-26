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
use Hyperf\Phar\Ast\Visitor\RewriteConfigVisitor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class VisitorTest extends TestCase
{
    public function testRewriteConfig()
    {
        $code = "<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
use Hyperf\\Contract\\StdoutLoggerInterface;
use Psr\\Log\\LogLevel;

use function Hyperf\\Support\\env;

return [
    'app_name' => env('APP_NAME', 'skeleton'),
    'app_env' => env('APP_ENV', 'dev'),
    'scan_cacheable' => env('SCAN_CACHEABLE', false),
    StdoutLoggerInterface::class => [
        'log_level' => [
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::DEBUG,
            LogLevel::EMERGENCY,
            LogLevel::ERROR,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
        ],
    ],
];
";
        $code = (new Ast())->parse($code, [new RewriteConfigVisitor()]);
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
use Hyperf\\Contract\\StdoutLoggerInterface;
use Psr\\Log\\LogLevel;
use function Hyperf\\Support\\env;
\$result = ['app_name' => env('APP_NAME', 'skeleton'), 'app_env' => env('APP_ENV', 'dev'), 'scan_cacheable' => env('SCAN_CACHEABLE', false), StdoutLoggerInterface::class => ['log_level' => [LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::DEBUG, LogLevel::EMERGENCY, LogLevel::ERROR, LogLevel::INFO, LogLevel::NOTICE, LogLevel::WARNING]]];
return array_replace(\$result, array('scan_cacheable' => true));", $code);
    }
}
