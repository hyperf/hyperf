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
namespace Hyperf\JsonRpcClient;

use Hyperf\Contract\PackerInterface;
use Hyperf\JsonRpcClient\Exception\RecvFailedException;
use Hyperf\JsonRpcClient\Exception\ServerException;
use Hyperf\JsonRpcClient\Transporter\TransporterInterface;

abstract class Client
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
     * @var DataFormatter
     */
    protected $formatter;

    /**
     * @var PathGenerator
     */
    protected $generator;

    /**
     * @var TransporterInterface
     */
    protected $transporter;

    public function __construct(string $service, TransporterInterface $transporter, PackerInterface $packer)
    {
        $this->service = $service;
        $this->packer = $packer;
        $this->transporter = $transporter;
        $this->formatter = new DataFormatter();
        $this->generator = new PathGenerator();
    }

    public function __call($name, $arguments)
    {
        $path = $this->generator->generate($this->service, $name);
        $data = $this->formatter->formatRequest($path, $arguments, $id = uniqid());
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
