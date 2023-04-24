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
namespace Hyperf\Swagger;

use Hyperf\Codec\Json;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Swagger\Processor\BuildPathsProcessor;
use OpenApi\Processors;

class Generator
{
    public function __construct(public ConfigInterface $config)
    {
    }

    public function generate(): void
    {
        $paths = $this->config->get('swagger.scan.paths', null);
        if ($paths === null) {
            $paths = $this->config->get('annotations.scan.paths', []);
        }

        $generator = new \OpenApi\Generator();
        $openapi = $generator->setAliases(\OpenApi\Generator::DEFAULT_ALIASES)
            ->setNamespaces(\OpenApi\Generator::DEFAULT_NAMESPACES)
            ->setProcessors([
                new Processors\DocBlockDescriptions(),
                new Processors\MergeIntoOpenApi(),
                new Processors\MergeIntoComponents(),
                new Processors\ExpandClasses(),
                new Processors\ExpandInterfaces(),
                new Processors\ExpandTraits(),
                new Processors\ExpandEnums(),
                new Processors\AugmentSchemas(),
                new Processors\AugmentProperties(),
                new BuildPathsProcessor(),
                new Processors\AugmentParameters(),
                new Processors\AugmentRefs(),
                new Processors\MergeJsonContent(),
                new Processors\MergeXmlContent(),
                new Processors\OperationId(),
                new Processors\CleanUnmerged(),
            ])
            ->generate($paths, validate: false);

        $jsonArray = Json::decode($openapi->toJson());
        $paths = $jsonArray['paths'] ?? [];
        $jsonArray['paths'] = [];
        $result = [];
        foreach ($paths as $key => $path) {
            [$serverName, $key] = explode('|', $key, 2);
            if (empty($result[$serverName])) {
                $result[$serverName] = $jsonArray;
            }

            $result[$serverName]['paths'][$key] = $path;
        }

        $path = $this->config->get('swagger.json_dir', BASE_PATH . '/storage/swagger');

        foreach ($result as $serverName => $json) {
            if (! is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            file_put_contents(rtrim($path, '/') . '/' . $serverName . '.json', Json::encode($json));
        }
    }
}
