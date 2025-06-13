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

namespace Hyperf\ExceptionHandler\Handler;

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\SessionInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Stringable\Str;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run;
use Whoops\RunInterface;

use function Hyperf\Support\env;

class WhoopsExceptionHandler extends ExceptionHandler
{
    protected static array $preference = [
        'text/html' => PrettyPageHandler::class,
        'application/json' => JsonResponseHandler::class,
        'application/xml' => XmlResponseHandler::class,
    ];

    public function handle(Throwable $throwable, ResponsePlusInterface $response)
    {
        $whoops = new Run();
        [$handler, $contentType] = $this->negotiateHandler();

        $whoops->pushHandler($handler);
        $whoops->allowQuit(false);
        ob_start();
        $whoops->{RunInterface::EXCEPTION_HANDLER}($throwable);
        $content = ob_get_clean();
        return $response
            ->setStatus(500)
            ->addHeader('Content-Type', $contentType)
            ->setBody(new SwooleStream($content));
    }

    public function isValid(Throwable $throwable): bool
    {
        return env('APP_ENV') !== 'prod' && class_exists(Run::class);
    }

    protected function negotiateHandler()
    {
        $request = RequestContext::get();
        $accepts = $request->getHeaderLine('accept');
        foreach (self::$preference as $contentType => $handler) {
            if (Str::contains($accepts, $contentType)) {
                return [$this->setupHandler(new $handler()), $contentType];
            }
        }
        return [new PlainTextHandler(), 'text/plain'];
    }

    protected function setupHandler($handler)
    {
        if ($handler instanceof PrettyPageHandler) {
            $handler->handleUnconditionally(true);

            if (defined('BASE_PATH')) {
                $handler->setApplicationRootPath(BASE_PATH);
            }

            $request = RequestContext::get();
            $handler->addDataTableCallback('PSR7 Query', [$request, 'getQueryParams']);
            $handler->addDataTableCallback('PSR7 Post', [$request, 'getParsedBody']);
            $handler->addDataTableCallback('PSR7 Server', [$request, 'getServerParams']);
            $handler->addDataTableCallback('PSR7 Cookie', [$request, 'getCookieParams']);
            $handler->addDataTableCallback('PSR7 File', [$request, 'getUploadedFiles']);
            $handler->addDataTableCallback('PSR7 Attribute', [$request, 'getAttributes']);

            $session = Context::get(SessionInterface::class);
            if ($session) {
                $handler->addDataTableCallback('Hyperf Session', [$session, 'all']);
            }
        } elseif ($handler instanceof JsonResponseHandler) {
            $handler->addTraceToOutput(true);
        }

        return $handler;
    }
}
