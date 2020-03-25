<?php
namespace Phar\Core;

class Analyzer
{
    protected $Path;
    public function __construct(Path $path)
    {
        $this->Path = $path;
    }

    public function getConfigsSerializeStr():string{
        try {
            return serialize($this->findHyperfConfigsArray());
        }catch (\Throwable $e){
            println("Serialize Exception: ".$e->getMessage());
        }
    }

    private function findHyperfConfigsArray():array {
        $config = $this->readConfig($this->Path->getConfigsPath() . '/config.php');
        $serverConfig = $this->readConfig($this->Path->getConfigsPath() .'/server.php');
        $autoloadConfig = $this->readPaths($this->Path->getConfigsPath() . '/autoload');
        $providerConfig = $this->getProviderConfig();
        $merged = array_merge_recursive($providerConfig, $serverConfig, $config, ...$autoloadConfig);
        return $merged;
    }

    private function getProviderConfig(){
        $providers = Composer::getMergedExtra('hyperf')['config'];
        return $this->loadProviders($providers);
    }

    private function loadProviders(array $providers): array
    {
        $providerConfigs = [];
        foreach ($providers ?? [] as $provider) {
            if (is_string($provider) && class_exists($provider) && method_exists($provider, '__invoke')) {
                $providerConfigs[] = (new $provider())();
            }
        }

        return $this->merge(...$providerConfigs);
    }

    private function merge(...$arrays): array
    {
        $result = array_merge_recursive(...$arrays);
        if (isset($result['dependencies'])) {
            $dependencies = array_column($arrays, 'dependencies');
            $result['dependencies'] = array_merge(...$dependencies);
        }

        return $result;
    }

    private function readConfig(string $configPath): array
    {
        $config = [];
        if (file_exists($configPath) && is_readable($configPath)) {
            $config = require $configPath;
        }
        return is_array($config) ? $config : [];
    }

    private function readPaths(string $path)
    {
        $configs = [];
        $files = scandir($path);
        $autoLoadPath = $this->Path->getConfigsPath() . '/autoload/';
        foreach ($files as $file) {
            if($file != '.' && $file != '..'){
                $configs[] = [
                    str_replace('.php','',$file) =>require $autoLoadPath . $file
                ];
            }
        }
        return $configs;
    }
}