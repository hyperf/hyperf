<?php


namespace Hyperf\Di\Aop;


interface ArroundInterface
{

    public function process(ProceedingJoinPoint $proceedingJoinPoint);

}