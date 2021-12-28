<?php

namespace HyperfTest\HttpServer\Stub;

use Swoole\Http\Response;

class SwooleResponseStub extends Response
{
    public $status = 0;

    public $headers = [];

    public $filename = '';

    public $contents = null;

    public function status($http_code, $reason = null)
    {
        $this->status = $http_code;
    }

    public function header($key, $value, $format = null)
    {
        $this->headers[$key] = $value;
    }

    public function sendfile($filename, $offset = null, $length = null)
    {
        $this->filename = $filename;
    }

    public function write($content)
    {
        $this->contents .= $content;
    }

    public function end($content = '')
    {
        $this->contents .= ($content."\r\n");
    }
}
