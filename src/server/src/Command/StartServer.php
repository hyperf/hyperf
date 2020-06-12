<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Server\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\ServerFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Runtime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartServer extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;
    private $serverFactory;
    private $serverConfig;

    protected function configure()
    {
        parent::configure();
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Mapping mainline server port. (priority)');
        $this->addOption('servers', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Mapping servers port. "name:port"');
        $this->addUsage('--port 9503');
        $this->addUsage('--servers http:9504');
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('start');
        $this->setDescription('Start hyperf servers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkEnvironment($output);

        $this->serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));

        $this->serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
        if (! $this->serverConfig) {
            throw new InvalidArgumentException('At least one server should be defined.');
        }

        if ($input->hasOption('servers') && !empty($input->getOption('servers'))) {
            $serversOption = $input->getOption('servers');
            foreach ($serversOption as $item) {
                if (preg_match('#(\w+)\:(\d+)#', $item, $match)) {
                    [, $name, $port] = $match;
                    $this->mappingPort($name, (int)$port);
                }
            }
        }

        if ($input->hasOption('port') && is_numeric($input->getOption('port'))) {
            $this->mappingPort('http', (int)$input->getOption('port'));
        }

        $this->serverFactory->configure($this->serverConfig);

        Runtime::enableCoroutine(true, swoole_hook_flags());

        $this->serverFactory->start();
    }

    private function mappingPort(string $name, int $port)
    {
        $key = array_search($name, array_column($this->serverConfig['servers'], 'name'));
        if (isset($this->serverConfig['servers'][$key])) {
            $this->serverFactory->getLogger()->warning(sprintf('The "%s" server port: %d, then env port: %d', $name, $port, $this->serverConfig['servers'][$key]['port']));
            $this->serverConfig['servers'][$key]['port'] = $port;
        }
    }

    private function checkEnvironment(OutputInterface $output)
    {
        /**
         * swoole.use_shortname = true       => string(1) "1"     => enabled
         * swoole.use_shortname = "true"     => string(1) "1"     => enabled
         * swoole.use_shortname = on         => string(1) "1"     => enabled
         * swoole.use_shortname = On         => string(1) "1"     => enabled
         * swoole.use_shortname = "On"       => string(2) "On"    => enabled
         * swoole.use_shortname = "on"       => string(2) "on"    => enabled
         * swoole.use_shortname = 1          => string(1) "1"     => enabled
         * swoole.use_shortname = "1"        => string(1) "1"     => enabled
         * swoole.use_shortname = 2          => string(1) "1"     => enabled
         * swoole.use_shortname = false      => string(0) ""      => disabled
         * swoole.use_shortname = "false"    => string(5) "false" => disabled
         * swoole.use_shortname = off        => string(0) ""      => disabled
         * swoole.use_shortname = Off        => string(0) ""      => disabled
         * swoole.use_shortname = "off"      => string(3) "off"   => disabled
         * swoole.use_shortname = "Off"      => string(3) "Off"   => disabled
         * swoole.use_shortname = 0          => string(1) "0"     => disabled
         * swoole.use_shortname = "0"        => string(1) "0"     => disabled
         * swoole.use_shortname = 00         => string(2) "00"    => disabled
         * swoole.use_shortname = "00"       => string(2) "00"    => disabled
         * swoole.use_shortname = ""         => string(0) ""      => disabled
         * swoole.use_shortname = " "        => string(1) " "     => disabled.
         */
        $useShortname = ini_get_all('swoole')['swoole.use_shortname']['local_value'];
        $useShortname = strtolower(trim(str_replace('0', '', $useShortname)));
        if (! in_array($useShortname, ['', 'off', 'false'], true)) {
            $output->writeln('<error>ERROR</error> Swoole short name have to disable before start server, please set swoole.use_shortname = off into your php.ini.');
            exit(0);
        }
    }
}
