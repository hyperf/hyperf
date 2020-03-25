<?php
namespace Phar\Core;

use Phar\template\ConfigFactoryTemplate;

class Builder
{
    protected $buildConfig;
    public function __construct(BuildConfig $buildConfig)
    {
        $this->buildConfig = $buildConfig;
    }

    public function build(){
        $this->beforeBuild();
        println("Hyperf framework build phar tool");
        println("Start building...");
        $phar = new \Phar($this->buildConfig->getOutput());
        $phar->startBuffering();
        $phar->buildFromDirectory($this->buildConfig->getInput(), $this->buildConfig->getRegex());
        $phar->setStub('#!/usr/bin/env php' . "\n" . $phar->createDefaultStub(str_replace('\\', '/', $this->buildConfig->getBootstrap())));
        $phar->stopBuffering();
        $this->afterBuild();
        println("Build successfully!");
        println(" - Phar file: {$this->buildConfig->getOutput()}");
    }

    private function beforeBuild(){
        $configFactoryPath = $this->buildConfig->getPath()->getConfigFactoryPath();
        rename($configFactoryPath . '/ConfigFactory.php',$configFactoryPath . '/ConfigFactory.php.bak');
        sleep(1);
        file_put_contents($configFactoryPath . '/ConfigFactory.php',(new ConfigFactoryTemplate())->buildContent($this->buildConfig->getConfigSerializeStr()));
    }

    private function afterBuild(){
        $configFactoryPath = $this->buildConfig->getPath()->getConfigFactoryPath();
        unlink($configFactoryPath . '/ConfigFactory.php');
        rename($configFactoryPath . '/ConfigFactory.php.bak',$configFactoryPath . '/ConfigFactory.php');
    }
}