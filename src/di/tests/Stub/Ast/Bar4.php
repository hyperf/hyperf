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

namespace HyperfTest\Di\Stub\Ast;

class Bar4 extends Bar
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }

    public function getId(): int
    {
        return parent::getId();
    }

    public static function getItems()
    {
        return parent::$items;
    }

    public function closure()
    {
        value(function () {
            parent::getId();
        });
    }
}
