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
namespace Hyperf\Encryption\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Encryption\Encrypter;
use Symfony\Component\Console\Input\InputArgument;

class EncrypterCommand extends Command
{
    public function __construct(protected ConfigInterface $config)
    {
        parent::__construct('gen:key');
    }

    protected function configure()
    {
        $this->setDescription('Generate the encryption key');
        $this->addArgument('cipher', InputArgument::OPTIONAL, 'The algorithm used for encryption.');

        parent::configure();
    }

    public function handle()
    {
        $cipher = $this->input->getArgument('cipher') ?: $this->config->get('encryption.cipher');

        $key = 'base64:' . base64_encode(Encrypter::generateKey($cipher));

        $this->line($key);
    }
}
