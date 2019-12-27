<?php
/**
 * @notice Install https://github.com/nauxliu/opencc4php extension before use this script.
 * Run this PHP script to generate the zh-tw and zh-hk doucmentations via zh-cn.
 */

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require BASE_PATH . '/vendor/autoload.php';

use Symfony\Component\Finder\Finder;

$config = [
    'zh-tw' => [
        'targetDir' => BASE_PATH . '/doc/zh-tw/',
        'rule' => 's2twp.json',
    ],
    'zh-hk' => [
        'targetDir' => BASE_PATH . '/doc/zh-hk/',
        'rule' => 's2hk.json',
    ],
];

$finder = new Finder();
$finder->files()->in(BASE_PATH . '/doc/zh-cn');

foreach ($config as $key => $item) {
    $od = opencc_open($item['rule']);
    foreach ($finder as $fileInfo) {
        $targetPath = $item['targetDir'] . $fileInfo->getRelativePath();
        $isCreateDir = false;
        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0777, true);
            chmod($targetPath, 0777);
            $isCreateDir = true;
        }
        if (! is_writable($targetPath)) {
            echo sprintf('Target path %s is not writable.' . PHP_EOL, $targetPath);
        }
        if ($fileInfo->getExtension() === 'md') {
            $translated = opencc_convert($fileInfo->getContents(), $od);
            $translated = str_replace('](zh-cn/', '](' . $key . '/', $translated);
            $targetTranslatedPath = $item['targetDir'] . $fileInfo->getRelativePathname();
            @file_put_contents($targetTranslatedPath, $translated);
        } else {
            $targetTranslatedPath = $item['targetDir'] . $fileInfo->getRelativePathname();
            @copy($fileInfo->getRealPath(), $targetTranslatedPath);
        }
    }
    opencc_close($od);
}
