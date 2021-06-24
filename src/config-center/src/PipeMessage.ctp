<?php

namespace Hyperf\ConfigCenter;


use Hyperf\ConfigCenter\Contract\PipeMessageInterface;

class PipeMessage implements PipeMessageInterface
{

    /**
     * @var array
     */
    protected $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}