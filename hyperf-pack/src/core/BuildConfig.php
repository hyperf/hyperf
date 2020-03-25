<?php


namespace Phar\Core;


class BuildConfig
{
    protected $output;

    protected $bootstrap;

    protected $input;

    protected $path;

    protected $configSerializeStr;

    protected $regex;

    /**
     * @return mixed
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @param mixed $regex
     */
    public function setRegex($regex): void
    {
        $this->regex = $regex;
    }

    /**
     * @return mixed
     */
    public function getConfigSerializeStr()
    {
        return $this->configSerializeStr;
    }

    /**
     * @param mixed $configSerializeStr
     */
    public function setConfigSerializeStr($configSerializeStr): void
    {
        $this->configSerializeStr = $configSerializeStr;
    }

    public function __construct(Path $path)
    {
        $this->path = $path;
        $this->initConfig($this->path);
    }

    private function initConfig(Path $path):void{
        $this->bootstrap = $path->getBootstrap();
        $this->input = $path->getHyperfPath();
        $this->output = $path->getOutputPath();
    }

    /**
     * @return Path
     */
    public function getPath(): Path
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getOutput():string
    {
        return $this->output;
    }

    /**
     * @return mixed
     */
    public function getBootstrap():string
    {
        return $this->bootstrap;
    }

    /**
     * @return mixed
     */
    public function getInput():string
    {
        return $this->input;
    }


}