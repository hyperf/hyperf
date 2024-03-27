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

namespace HyperfTest\Validation\File;

use Hyperf\Testing\HttpMessage\Upload\UploadedFile;

class File extends UploadedFile
{
    /**
     * The temporary file resource.
     *
     * @var resource
     */
    public $tempFile;

    /**
     * The "size" to report.
     */
    public int $sizeToReport = 0;

    /**
     * Create a new file instance.
     *
     * @param resource $tempFile
     */
    public function __construct(
        public string $name,
        $tempFile,
        private int $error = 0,
        private ?string $mimeType = null
    ) {
        $this->tempFile = $tempFile;

        parent::__construct(
            $this->tempFilePath(),
            $this->sizeToReport,
            $this->error,
            $this->name,
            $this->mimeType
        );
    }

    /**
     * Create a new fake file.
     * @param null|mixed $clientFilename
     * @param null|mixed $clientMediaType
     */
    public static function create(string $name, int|string $kilobytes = 0, int $error = 0, $clientFilename = null, $clientMediaType = null): File
    {
        return (new FileFactory())->create($name, $kilobytes, $error, $clientFilename, $clientMediaType);
    }

    /**
     * Create a new fake file with content.
     */
    public static function createWithContent(string $name, string $content): File
    {
        return (new FileFactory())->createWithContent($name, $content);
    }

    /**
     * Create a new fake image.
     */
    public static function image(string $name, int $width = 10, int $height = 10): File
    {
        return (new FileFactory())->image($name, $width, $height);
    }

    /**
     * Set the "size" of the file in kilobytes.
     *
     * @return $this
     */
    public function size(int $kilobytes): static
    {
        $this->sizeToReport = $kilobytes * 1024;

        return $this;
    }

    /**
     * Get the size of the file.
     */
    public function getSize(): int
    {
        return $this->sizeToReport ?: parent::getSize();
    }

    /**
     * Set the "MIME type" for the file.
     *
     * @return $this
     */
    public function mimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get the MIME type of the file.
     */
    public function getMimeType(): string
    {
        return $this->mimeType ?: MimeType::from($this->name);
    }

    /**
     * Get the path to the temporary file.
     */
    protected function tempFilePath(): string
    {
        return stream_get_meta_data($this->tempFile)['uri'];
    }
}
