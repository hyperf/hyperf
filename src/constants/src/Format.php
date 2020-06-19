<?php
declare(strict_types=1);

namespace Hyperf\Constants;

interface Format
{
    public function parse($code, $value): array;
}
