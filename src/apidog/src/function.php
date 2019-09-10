<?php
declare(strict_types = 1);

use Hyperf\Utils\Str;

function array_get_node($key, $arr = [], $default = null)
{
    $path = explode('.', $key);
    foreach ($path as $key) {
        $key = trim($key);
        if (empty($arr) || !isset($arr[$key])) {
            return $default;
        }
        $arr = $arr[$key];
    }

    return $arr;
}

function is_json_str($str, $comment_mode = false) {
    if ($comment_mode) {
        $str = preg_replace('@//[^"]+?$@mui', '', $str);
        $str = preg_replace('@^\s*//.*?$@mui', '', $str);
    }
    $lint = (new JsonParser())->lint($str);
    return $lint ? $lint->getMessage() : $lint;
}

function controller_name_path($className, $prefix = null)
{
    if (! $prefix) {
        $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, '\\Controller\\'));
        $handledNamespace = str_replace('\\', '/', $handledNamespace);
        $prefix = Str::snake($handledNamespace);
        $prefix = str_replace('/_', '/', $prefix);
    }
    if ($prefix[0] !== '/') {
        $prefix = '/' . $prefix;
    }
    return $prefix;
}
