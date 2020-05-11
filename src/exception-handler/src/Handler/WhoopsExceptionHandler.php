<?php


namespace Hyperf\ExceptionHandler\Handler;


use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Context;
use Hyperf\Utils\Str;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;

class WhoopsExceptionHandler extends ExceptionHandler
{
    protected static $preference = [
        'text/html' => PrettyPageHandler::class,
        'application/json' => JsonResponseHandler::class,
        'application/xml' => XmlResponseHandler::class,
    ];

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $whoops = new Run();
        [$handler, $contentType, $request] = $this->negotiateHandler();

        // CLI mode restriction hack
        if (method_exists($handler, 'handleUnconditionally')) {
            $handler->handleUnconditionally(true);
        }

        $whoops->pushHandler($handler);
        $whoops->allowQuit(false);
        ob_start();
        $whoops->{Run::EXCEPTION_HANDLER}($throwable, $request);
        $content = ob_get_clean();
        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', $contentType)
            ->withBody(new SwooleStream($content));
    }

    public function isValid(Throwable $throwable): bool
    {
        return env('APP_ENV') !== 'prod' && class_exists(Run::class);
    }

    private function negotiateHandler()
    {
        /** @var ServerRequestInterface $request */
        $request = Context::get(ServerRequestInterface::class);
        $accepts = $request->getHeaderLine('accept');
        foreach (self::$preference as $contentType => $handler) {
            if (Str::contains($accepts, $contentType)) {
                return [new $handler,  $contentType, $request];
            }
        }

        return [new PlainTextHandler(),  'text/plain'];
    }
}
