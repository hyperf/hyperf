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

use Hyperf\Contract\ConfigInterface;

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

        $openapi = \OpenApi\Generator::scan($paths, [
            'validate' => false,
        ]);

        $path = $this->config->get('swagger.json');

        file_put_contents($path, $openapi->toJson());
    }
}
