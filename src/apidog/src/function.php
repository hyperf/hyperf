<?php
declare(strict_types = 1);

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

function controllerNameToPath($className)
{
    $path = strtolower($className);
    $path = str_replace('\\', '/', $path);
    $path = str_replace('app/controller', '', $path);
    $path = str_replace('controller', '', $path);
    return $path;
}
