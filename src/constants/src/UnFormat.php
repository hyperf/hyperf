<?php
declare(strict_types=1);

namespace Hyperf\Constants;

class UnFormat implements Format
{
    public function parse($code, $value): array
    {
        return [$code => $value];
    }
}
