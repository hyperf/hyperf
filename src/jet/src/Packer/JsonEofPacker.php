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
namespace Hyperf\Jet\Packer;

use Hyperf\Contract\PackerInterface;

class JsonEofPacker implements PackerInterface
{
    /**
     * @var string
     */
    protected $eof;

    public function __construct(string $eof = "\r\n")
    {
        $this->eof = $eof;
    }

    public function pack($data): string
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $data . $this->eof;
    }

    public function unpack(string $data)
    {
        return json_decode($data, true);
    }
}
