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

namespace Hyperf\JsonRpc\Packer;

use Hyperf\Contract\PackerInterface;

class JsonLengthPacker implements PackerInterface
{
    protected string $type;

    protected int $length;

    protected array $defaultOptions = [
        'package_length_type' => 'N',
        'package_body_offset' => 4,
    ];

    public function __construct(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options['settings'] ?? []);

        $this->type = $options['package_length_type'];
        $this->length = $options['package_body_offset'];
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        return pack($this->type, strlen($data)) . $data;
    }

    public function unpack(string $data)
    {
        $data = substr($data, $this->length);
        if (! $data) {
            return null;
        }
        return json_decode($data, true);
    }
}
