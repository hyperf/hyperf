<?php


namespace Hyperflex\Di\Aop;


interface ArroundInterface
{

    public function process(ProceedingJoinPoint $proceedingJoinPoint);

}