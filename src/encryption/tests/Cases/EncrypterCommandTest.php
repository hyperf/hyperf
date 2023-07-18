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
namespace HyperfTest\Encryption\Cases;

use Hyperf\Config\Config;
use Hyperf\Encryption\Commands\EncrypterCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * @internal
 * @coversNothing
 */
class EncrypterCommandTest extends TestCase
{
    public function testGenKeyWithCipher()
    {
        $code = $this->runCommand(['cipher' => 'AES-256-GCM']);

        $this->assertSame($code, 0);
    }

    public function testGenKeyWithoutCipher()
    {
        $code = $this->runCommand([]);

        $this->assertSame($code, 0);
    }

    protected function runCommand(array $input)
    {
        $config = new Config([
            'encryption' => [
                'key' => '',
                'cipher' => 'AES-256-CBC',
            ],
        ]);

        $command = new EncrypterCommand($config);

        $input = new ArrayInput($input);
        $output = new ConsoleOutput();

        return $command->run($input, $output);
    }
}
