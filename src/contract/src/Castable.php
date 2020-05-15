<?php
declare(strict_types=1);

namespace Hyperf\Contract;


interface Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @return string|CastsAttributes|CastsInboundAttributes
     */
    public static function castUsing();
}