<?php
namespace Phar\Core;

class Path
{
    protected $hyperfPath;

    protected $configsPath;

    protected $configFactoryPath;

    protected $outputPath;

    protected $bootstrap;
    /**
     * @return mixed
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * @param mixed $outputPath
     */
    public function setOutputPath($outputPath)
    {
        $this->outputPath = $outputPath;
    }

    /**
     * @return mixed
     */
    public function getConfigFactoryPath()
    {
        return $this->configFactoryPath;
    }

    /**
     * @param mixed $configFactoryPath
     */
    public function setConfigFactoryPath($configFactoryPath): void
    {
        $this->configFactoryPath = $configFactoryPath;
    }


    public function __invoke($hyperfPath,$configsPath)
    {
        $this->hyperfPath = $hyperfPath;
        $this->configsPath = $configsPath;
    }

    /**
     * @return mixed
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }

    /**
     * @param mixed $bootstrap
     */
    public function setBootstrap($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }
    public function __construct($hyperfPath = '')
    {
        $this->hyperfPath = $hyperfPath;
        $this->initPath();
    }

    private function initPath(){
        $this->configFactoryPath = str_replace('//','/',$this->hyperfPath . '/vendor/hyperf/config/src/');
        $this->configsPath = str_replace('//','/',$this->hyperfPath . '/config');
    }

    public function getHyperfPath():string
    {
        return $this->hyperfPath;
    }

    public function setHyperfPath($hyperfPath)
    {
        $this->hyperfPath = $hyperfPath;
    }

    /**
     * @return mixed
     */
    public function getConfigsPath():string
    {
        return $this->configsPath;
    }

    /**
     * @param mixed $configsPath
     */
    public function setConfigsPath($configsPath)
    {
        $this->configsPath = $configsPath;
    }

    public function setArgv(array $argv){
        $this->outputPath = $argv['output'];
        $this->bootstrap  = $argv['bootstrap'];
        $this->hyperfPath = $argv['dir'];
        $this->initPath();
    }

}