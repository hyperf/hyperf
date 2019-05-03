<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Rpc;

use Hyperf\Rpc\Contract\EofInterface;
use Hyperf\Rpc\Contract\ResponseInterface;

class Response extends \Hyperf\HttpMessage\Base\Response implements ResponseInterface, EofInterface
{
    /**
     * @var int
     */
    private $fd;

    /**
     * @var string
     */
    private $requestId = '';

    /**
     * @var string
     */
    private $eof;

    /**
     * @var null|array
     */
    private $error;

    public function __construct(int $fd)
    {
        $this->fd = $fd;
    }

    public function __toString()
    {
        $sendData = [
            'jsonrpc' => '2.0',
            'id' => $this->getRequestId(),
        ];
        if ($this->error) {
            $sendData['error'] = $this->error;
        } else {
            $sendData['result'] = $this->getBody()->getContents();
        }
        return json_encode($sendData) . $this->getEof();
    }

    public function getError(): array
    {
        return $this->error;
    }

    public function setError(int $code, string $message, $data = null): self
    {
        $this->error = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];
        return $this;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public function setRequestId($requestId): self
    {
        $this->requestId = $requestId;
        return $this;
    }

    public function getEof(): string
    {
        return $this->eof;
    }

    public function setEof($eof): self
    {
        $this->eof = $eof;
        return $this;
    }
}
