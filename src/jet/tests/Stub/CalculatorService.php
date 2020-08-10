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
namespace HyperfTest\Jet\Stub;

use Hyperf\Jet\AbstractClient;
use Hyperf\Jet\Packer\JsonEofPacker;
use Hyperf\Jet\Transporter\StreamSocketTransporter;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Contract\TransporterInterface;

class CalculatorService extends AbstractClient
{
    public function __construct(
        string $service = 'CalculatorService',
        TransporterInterface $transporter = null,
        PackerInterface $packer = null,
        ?DataFormatterInterface $dataFormatter = null,
        ?PathGeneratorInterface $pathGenerator = null
    ) {
        $transporter = new StreamSocketTransporter('127.0.0.1', 9503);
        $packer = new JsonEofPacker();
        parent::__construct($service, $transporter, $packer, $dataFormatter, $pathGenerator);
    }
}
