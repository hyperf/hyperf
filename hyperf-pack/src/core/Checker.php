<?php
namespace Phar\Core;

class Checker
{
    protected $path;
    public function __construct(Path $path)
    {
        $this->path = $path;
    }

    public function checkPaths():bool{
        $dir = $this->path->getConfigsPath();
        if(!is_dir($dir)){
            return false;
        }
        $path = $dir . '/config.php';
        if(!is_file($path) && is_readable($path)){
            return false;
        }
        if(!is_dir($this->path->getConfigFactoryPath())){
            return false;
        }
        if(!is_file($this->path->getConfigFactoryPath() . '/ConfigFactory.php') && is_readable($this->path->getConfigFactoryPath() . '/ConfigFactory.php')){
            return false;
        }
        return true;
    }

}