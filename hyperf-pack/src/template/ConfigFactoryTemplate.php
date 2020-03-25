<?php


namespace Phar\template;


class ConfigFactoryTemplate
{
    protected $phpCode;
    public function __construct()
    {
        $this->phpCode = <<<eof
<?php
declare(strict_types=1);
namespace Hyperf\Config;
class ConfigFactory
{
    public function __invoke()
    {
        return new Config(unserialize('%s'));
    }
}
eof;
    }

    public function buildContent($configStr){
        return sprintf($this->phpCode,$configStr);
    }

}