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
use Hyperf\Contract\OnRequestInterface;
use Hyperf\Engine\Http\Stream;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpServer\ResponseEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpServer implements OnRequestInterface
{
    protected array $metadata = [];

    protected array $config = [
        'enable' => false,
        'port' => 9500,
        'json_dir' => BASE_PATH . '/storage/swagger',
        'html' => null,
        'url' => '/swagger',
        'auto_generate' => false,
        'scan' => [
            'paths' => null,
        ],
    ];

    public function __construct(protected ContainerInterface $container, protected ResponseEmitter $emitter)
    {
        $this->config = array_merge($this->config, $this->container->get(ConfigInterface::class)->get('swagger', []));
    }

    public function onRequest($request, $response): void
    {
        if ($request instanceof ServerRequestInterface) {
            $psr7Request = $request;
        } else {
            $psr7Request = Psr7Request::loadFromSwooleRequest($request);
        }

        $path = $psr7Request->getUri()->getPath();
        if ($path === $this->config['url']) {
            $stream = new Stream($this->getHtml());
        } else {
            $stream = new Stream($this->getMetadata($path));
        }

        $psrResponse = (new Response())->withBody($stream);

        $this->emitter->emit($psrResponse, $response);
    }

    protected function getMetadata(string $path): string
    {
        $path = rtrim($this->config['json_dir'], '/') . $path;
        $id = md5($path);
        if (isset($this->metadata[$id])) {
            return $this->metadata[$id];
        }

        if (file_exists($path)) {
            $metadata = file_get_contents($path);
        } else {
            $metadata = Json::encode([
                'openapi' => '3.0.0',
            ]);
        }

        return $this->metadata[$id] = $metadata;
    }

    protected function getHtml(): string
    {
        if (! empty($this->config['html'])) {
            return $this->config['html'];
        }

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
    <link rel="stylesheet" href="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui.css" />
  </head>
  <body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
  <script src="https://unpkg.hyperf.wiki/swagger-ui-dist@4.5.0/swagger-ui-standalone-preset.js" crossorigin></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: GetQueryString("search"),
        dom_id: '#swagger-ui',
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: "StandaloneLayout",
      });
    };
    function GetQueryString(name) {
      var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
      var r = window.location.search.substr(1).match(reg); //获取url中"?"符后的字符串并正则匹配
      var context = "";
      if (r != null)
        context = decodeURIComponent(r[2]);
      reg = null;
      r = null;
      return context == null || context == "" || context == "undefined" ? "/http.json" : context;
    }
  </script>
  </body>
</html>
HTML;
    }
}
