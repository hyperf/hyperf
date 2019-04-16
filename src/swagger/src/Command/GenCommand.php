<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Swagger\Command;

use Hyperf\Framework\Annotation\Command;
use OpenApi\Analysis;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function OpenApi\scan;
use const OpenApi\UNDEFINED;

/**
 * @Command
 */
class GenCommand extends SymfonyCommand
{
    public function __construct()
    {
        parent::__construct('swagger:gen');
    }

    protected function configure()
    {
        $this->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The path that needs scan.', 'app/');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Path to store the generated documentation.', './');
        $this->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'The format of the generated documentation, supports yaml and json.', 'yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('path');
        $outputDir = $input->getOption('output');
        $format = $input->getOption('format');
        Analysis::registerProcessor(function (Analysis $analysis) {
            foreach ($analysis->openapi->paths ?? [] as $path) {
                if ($path->path !== UNDEFINED) {
                    continue;
                }
                switch ($path) {
                    case isset($path->get):
                        $operationId = $path->get->operationId;
                        if (strpos($operationId, '::') !== false) {
                            [$controller, $action] = explode('::', $operationId);
                            // @TODO Retrieve the path according to controller and action name, and then set the path to $path->path.
                        }
                        break;
                }
            }
        });
        $scanner = scan($path);
        $scanner->saveAs($outputDir . 'openapi.' . $format);
    }
}
