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

namespace Hyperf\Filesystem\Adapter;

use Hyperf\Filesystem\Contract\AdapterFactoryInterface;
use Hyperf\Filesystem\Version;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Ftp\ConnectivityCheckerThatCanFail;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use League\Flysystem\Ftp\NoopCommandConnectivityChecker;

class FtpAdapterFactory implements AdapterFactoryInterface
{
    public function make(array $options)
    {
        if (Version::isV2()) {
            $options = FtpConnectionOptions::fromArray($options);

            $connectivityChecker = new ConnectivityCheckerThatCanFail(new NoopCommandConnectivityChecker());

            return new FtpAdapter($options, null, $connectivityChecker);
        }
        return new Ftp($options);
    }
}
