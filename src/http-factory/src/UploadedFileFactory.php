<?php
declare(strict_types=1);
namespace Hyperf\HttpMessage\Factory;


use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        if ($stream instanceof SwooleFileStream) {
            return new UploadedFile($stream->getFilename(), $size, $error, $clientFilename, $clientMediaType);
        }
        throw new \InvalidArgumentException('Invalid stream');
    }

}