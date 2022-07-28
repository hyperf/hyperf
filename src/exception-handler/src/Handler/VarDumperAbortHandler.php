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

use ErrorException;
use Hyperf\ExceptionHandler\Exception\VarDumperAbort;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Throwable;

class VarDumperAbortHandler extends ExceptionHandler
{
    /**
     * @param VarDumperAbort $throwable
     *
     * @throws ErrorException
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();
        ob_start();
        $cloner = new VarCloner();
        $cloner->addCasters(ReflectionCaster::UNSET_CLOSURE_FILE_INFO);
        foreach ($throwable->vars as $var) {
            (new HtmlDumper())->dump($cloner->cloneVar($var));
        }

        return $response->withBody(new SwooleStream(ob_get_clean()));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof VarDumperAbort;
    }
}