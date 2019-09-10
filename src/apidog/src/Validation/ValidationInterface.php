<?php
namespace Hyperf\Apidog\Validation;

interface ValidationInterface
{
    public function check(array $rules, array $data, $obj = null, $key_tree = null);

    public function getError();
}