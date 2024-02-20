<?php
namespace PHPUnit\Logging;

use const FILE_APPEND;
use const LOCK_EX;
use const PHP_EOL;
use function file_put_contents;
use function implode;
use function preg_split;
use function str_repeat;
use function strlen;
use PHPUnit\Event\Event;
use PHPUnit\Event\Tracer\Tracer;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class EventLogger implements Tracer
{
    private readonly string $path;
    private readonly bool $includeTelemetryInfo;

    public function __construct(string $path, bool $includeTelemetryInfo)
    {
        $this->path = $path;
        $this->includeTelemetryInfo = $includeTelemetryInfo;
    }

    public function trace(Event $event): void
    {
        $telemetryInfo = $this->telemetryInfo($event);
        $indentation = PHP_EOL . str_repeat(' ', strlen($telemetryInfo));
        $lines = preg_split('/\r\n|\r|\n/', $event->asString());

        echo implode($indentation, $lines) . PHP_EOL;
    }

    private function telemetryInfo(Event $event): string
    {
        if (! $this->includeTelemetryInfo) {
            return '';
        }

        return $event->telemetryInfo()->asString() . ' ';
    }
}
