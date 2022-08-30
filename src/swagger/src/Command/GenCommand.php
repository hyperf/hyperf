<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Swagger\Command;

use Hyperf\Command\Command;
use OpenApi\Analysis;
use Symfony\Component\Console\Input\InputOption;

use function OpenApi\scan;

use const OpenApi\UNDEFINED;

class GenCommand extends Command
{
    protected ?string $name = 'swagger:gen';

    public function handle()
    {
        $path = $this->input->getOption('path');
        $outputDir = $this->input->getOption('output');
        $format = $this->input->getOption('format');
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
        $destnation = $outputDir . 'openapi.' . $format;
        $scanner->saveAs($destnation);
        $this->info(sprintf('[INFO] Written to %s successfully.', $destnation));
    }

    protected function getOptions(): array
    {
        return [
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'The path that needs scan.', 'app/'],
            ['output', 'o', InputOption::VALUE_OPTIONAL, 'Path to store the generated documentation.', './'],
            ['format', 'f', InputOption::VALUE_OPTIONAL, 'The format of the generated documentation, supports yaml and json.', 'yaml'],
        ];
    }
}
