<?php


namespace Hyperf\Lock\Factory;


interface LockInterface
{
    /**
     * @return mixed
     */
    public function lock(string $id);

    /**
     * @return mixed
     */
    public function unlock(string $id);
}
