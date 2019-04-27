<?php

namespace Hyperf\Rpc\Contract;


interface TransporterInterface
{

    public function send(string $data);

}