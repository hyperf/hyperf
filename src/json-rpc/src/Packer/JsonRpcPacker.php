<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\JsonRpc\Packer;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\PackerInterface;

class JsonRpcPacker implements PackerInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    protected $protocol = 'jsonrpc';

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($this->isLengthCheck()) {
            return pack($this->getPackType(), strlen($data)) . $data;
        }

        return $data . $this->getEof();
    }

    public function unpack(string $data)
    {
        if ($this->isLengthCheck()) {
            $data = substr($data, $this->getHeadLength());
        }

        return json_decode($data, true);
    }

    protected function isLengthCheck(): bool
    {
        return (bool) $this->config->get('json_rpc.transporter.tcp.options.open_length_check', false);
    }

    protected function getEof(): string
    {
        return $this->config->get('json_rpc.transporter.tcp.options.package_eof', "\r\n");
    }

    protected function getPackType(): string
    {
        return $this->config->get('json_rpc.transporter.tcp.options.package_length_type', 'N');
    }

    protected function getHeadLength(): int
    {
        $options = array_merge($this->config->get('json_rpc.transporter.tcp.options', []), [
            'package_length_offset' => 0,
            'package_body_offset' => 4,
        ]);

        return $options['package_length_offset'] + $options['package_body_offset'];
    }
}
