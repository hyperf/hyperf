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

class JsonEofPacker implements PackerInterface
{
    /**
     * @var string
     */
    protected $eof;

    public function __construct(array $options = [])
    {
        $this->eof = $options['settings']['package_eof'] ?? "\r\n";
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $data . $this->eof;
    }

    public function unpack(string $data)
    {
        $data = rtrim($data, $this->eof);
        return json_decode($data, true);
    }
}
