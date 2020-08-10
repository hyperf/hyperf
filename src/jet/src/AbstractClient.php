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
namespace Hyperf\Jet;

use Hyperf\Jet\DataFormatter\DataFormatter;
use Hyperf\Jet\Exception\RecvFailedException;
use Hyperf\Jet\Exception\ServerException;
use Hyperf\Jet\PathGenerator\PathGenerator;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Contract\TransporterInterface;

abstract class AbstractClient
{
    /**
     * @var null|resource
     */
    protected $client;

    /**
     * @var string
     */
    protected $service;

    /**
     * @var PackerInterface
     */
    protected $packer;

    /**
     * @var DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var PathGeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var TransporterInterface
     */
    protected $transporter;

    public function __construct(string $service, TransporterInterface $transporter, PackerInterface $packer, ?DataFormatterInterface $dataFormatter = null, ?PathGeneratorInterface $pathGenerator = null)
    {
        $this->service = $service;
        $this->packer = $packer;
        $this->transporter = $transporter;
        is_null($dataFormatter) && $dataFormatter = new DataFormatter();
        $this->dataFormatter = $dataFormatter;
        is_null($pathGenerator) && $pathGenerator = new PathGenerator();
        $this->pathGenerator = $pathGenerator;
    }

    public function __call($name, $arguments)
    {
        $path = $this->pathGenerator->generate($this->service, $name);
        $data = $this->dataFormatter->formatRequest([$path, $arguments, uniqid()]);
        $this->transporter->send($this->packer->pack($data));
        $ret = $this->transporter->recv();
        if (! is_string($ret)) {
            throw new RecvFailedException();
        }

        $data = $this->packer->unpack($ret);

        if (array_key_exists('result', $data)) {
            return $data['result'];
        }

        throw new ServerException($data['error'] ?? []);
    }
}
