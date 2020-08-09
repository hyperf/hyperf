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
namespace Hyperf\Jet\Transporter;

use Hyperf\Jet\Exception\ConnectionException;

class StreamSocketTransporter implements TransporterInterface
{
    /**
     * @var null|resource
     */
    protected $client;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var float
     */
    protected $timeout;

    public function __construct(string $ip, int $port, float $timeout = 1.0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->timeout = $timeout;

        $this->connect();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function send(string $data)
    {
        fwrite($this->client, $data);
    }

    public function recv()
    {
        return fread($this->client, 65535);
    }

    protected function connect()
    {
        if ($this->client) {
            fclose($this->client);
            unset($this->client);
        }

        $client = stream_socket_client("tcp://{$this->ip}:{$this->port}", $errno, $errstr, $this->timeout);
        if ($client === false) {
            throw new ConnectionException(sprintf('[%d] %s', $errno, $errstr));
        }

        $this->client = $client;
    }

    protected function close()
    {
        if ($this->client) {
            fclose($this->client);
            $this->client = null;
        }
    }
}
