<?php


namespace Phar;


use Mix\Console\CommandLine\Argument;
use Mix\Console\CommandLine\Flag;
use Phar\Core\Analyzer;
use Phar\Core\BuildConfig;
use Phar\Core\Builder;
use Phar\Core\Checker;
use Phar\Core\Path;

class Main
{
    protected $commands;
    public function __construct($commands)
    {
        $this->commands = $commands['commands'];
    }

    public function run(){
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('Please run in CLI mode.');
        }
        // phar.readonly检查
        if (ini_get('phar.readonly')) {
            println("Please use 'php -d phar.readonly=0 hyperf-pack.phar build opt...'");
            exit;
        }
        Flag::init();
        // 获取参数
        $argv = [
            'dir'       => Flag::string(['d', 'dir'], ''),
            'output'    => Flag::string(['o', 'output'], ''),
            'bootstrap' => Flag::string(['b', 'bootstrap'], ''),
            'regex'     => Flag::string(['r', 'regex'], ''),
        ];
        //参数判断
        if (Argument::subCommand() == '' && Argument::command() == '') {
            if (Flag::bool(['h', 'help'], false)) {
                $this->help();
                return;
            }
            if (Flag::bool(['v', 'version'], false)) {
                $this->version();
                return;
            }
            $options = Flag::options();
            if (empty($options)) {
                $this->help();
                return;
            }
            $keys   = array_keys($options);
            $flag   = array_shift($keys);
            $script = Argument::script();
            println("flag provided but not defined: '{$flag}', see '{$script} --help'.");
            return;
        }
        if ((Argument::command() !== '' || Argument::subCommand() !== '') && Flag::bool(['h', 'help'], false)) {
            $this->commandHelp();
            return;
        }
        $this->setPath($argv['dir']);
        $this->startBuild($argv);
    }


    /**
     * 版本
     */
    protected function version()
    {
        $appName          = 'hyperf';
        $appVersion       = '1.0.0';
        $frameworkVersion = '1.0.0';
        println("{$appName} version {$appVersion}, framework version {$frameworkVersion}");
    }

    /**
     * 帮助
     */
    protected function help()
    {
        $script = Argument::script();
        println("Usage: {$script} [OPTIONS] COMMAND [SUBCOMMAND] [opt...]");
        $this->printOptions();
        $this->printCommands();
        println('');
        println("Run '{$script} COMMAND [SUBCOMMAND] --help' for more information on a command.");
        println('');
        println("Developed with Hyperf framework. (hyperf.io)");
    }

    /**
     * 有子命令
     * @return bool
     */
    protected function hasSubCommand()
    {
        foreach ($this->commands as $key => $item) {
            if (strpos($key, ' ') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * 打印选项列表
     */
    protected function printOptions()
    {
        $tabs = $this->hasSubCommand() ? "\t\t" : "\t";
        println('');
        println('Options:');
        println("  -h, --help{$tabs}Print usage");
        println("  -v, --version{$tabs}Print version information");
    }

    /**
     * 打印命令列表
     */
    protected function printCommands()
    {
        println('');
        println('Commands:');
        foreach ($this->commands as $key => $item) {
            $command     = $key;
            $subCommand  = '';
            $description = $item['description'] ?? '';
            if (strpos($key, ' ') !== false) {
                list($command, $subCommand) = explode(' ', $key);
            }
            if ($subCommand == '') {
                println("  {$command}\t{$description}");
            } else {
                println("  {$command} {$subCommand}\t{$description}");
            }
        }
    }

    /**
     * 打印命令选项列表
     */
    protected function printCommandOptions()
    {
        $options = $this->commands['build']['options'];
        println('');
        println('Options:');
        foreach ($options as $option) {
            $names = array_shift($option);
            if (is_string($names)) {
                $names = [$names];
            }
            $flags = [];
            foreach ($names as $name) {
                if (strlen($name) == 1) {
                    $flags[] = "-{$name}";
                } else {
                    $flags[] = "--{$name}";
                }
            }
            $flag        = implode(', ', $flags);
            $description = $option['description'] ?? '';
            println("  {$flag}\t{$description}");
        }
    }

    /**
     * 命令帮助
     */
    protected function commandHelp()
    {
        $script  = Argument::script();
        $command = trim(implode(' ', [Argument::command(), Argument::subCommand()]));
        println("Usage: {$script} {$command} [opt...]");
        $this->printCommandOptions();
        println("Developed with Hyperf framework. (hyperf.io)");
    }

    protected function setPath($path){
        !defined('BASE_PATH') && define('BASE_PATH', $path);
    }

    protected function validateArgv($argv){
        foreach ($argv as $key=>$value){
        }
    }

    protected function startBuild($argv){
        $path = new Path();
        $path->setArgv($argv);
        $checker = new Checker($path);
        if(!$checker->checkPaths()){
            println('dir path error,please check');
        }else{
            $analyzer    = new Analyzer($path);
            $buildConfig = new BuildConfig($path);
            $buildConfig->setRegex($argv['regex']);
            $buildConfig->setConfigSerializeStr($analyzer->getConfigsSerializeStr());
            $builder = new Builder($buildConfig);
            $builder->build();
        }
    }
}