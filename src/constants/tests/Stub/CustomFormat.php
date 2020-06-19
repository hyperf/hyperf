<?php
declare(strict_types=1);

namespace HyperfTest\Constants\Stub;

use Hyperf\Constants\Format;

class CustomFormat implements Format
{
    public function parse($code, $value): array
    {
        return [['code' => $code, 'msg' => $value]];
    }
}
