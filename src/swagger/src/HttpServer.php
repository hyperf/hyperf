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
use Hyperf\Contract\OnRequestInterface;
use Hyperf\Engine\Http\Stream;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\ResponseEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpServer implements OnRequestInterface
{
    protected string $json = '';

    public function __construct(protected ContainerInterface $container, protected ResponseEmitter $emitter)
    {
    }

    public function onRequest($request, $response): void
    {
        if ($request instanceof ServerRequestInterface) {
            $psr7Request = $request;
        } else {
            $psr7Request = Psr7Request::loadFromSwooleRequest($request);
        }

        $path = $psr7Request->getUri()->getPath();
        if (str_ends_with($path, '.json')) {
            $stream = new Stream($this->getJson());
        } else {
            $stream = new Stream($this->getHtml());
        }

        $psrResponse = (new Response())->withBody($stream);

        $this->emitter->emit($psrResponse, $response);
    }

    private function getJson(): string
    {
        if ($this->json) {
            return $this->json;
        }

        $config = $this->container->get(ConfigInterface::class);

        return $this->json = file_get_contents(BASE_PATH . $config->get('swagger.swagger_json', '/storage/openapi.json'));
    }

    private function getHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta
      name="description"
      content="SwaggerUI"
    />
    <title>SwaggerUI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui.css" />
  </head>
  <body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
  <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" crossorigin></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: '/openapi.json',
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: "StandaloneLayout",
      });
    };
  </script>
  </body>
</html>
HTML;
    }
}
