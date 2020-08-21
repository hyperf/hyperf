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
namespace Hyperf\Nacos\Utils;

class Arr
{
    public function array_merge_deep($arr1, $arr2){
        $merged	= $arr1;
        foreach($arr2 as $key => &$value){
            if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                $merged[$key]	= $this->array_merge_deep($merged[$key], $value);
            }elseif(is_numeric($key)){
                if(!in_array($value, $merged)) {
                    $merged[]	= $value;
                }
            }else{
                $merged[$key]	= $value;
            }
        }
        return $merged;
    }

}