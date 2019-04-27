<?php

namespace Hyperf\Rpc;


class Client
{

    /**
     * @var Contract\PackerInterface
     */
    private $packer;

    /**
     * @var Contract\TransporterInterface
     */
    private $transporter;

    public function send($data)
    {
        $packedData = $this->packer->pack($data);
        $this->transporter->send($packedData);
    }

}