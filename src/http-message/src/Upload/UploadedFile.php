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

namespace Hyperf\HttpMessage\Upload;

use Hyperf\HttpMessage\Stream\StandardStream;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use SplFileInfo;
use Stringable;

class UploadedFile extends SplFileInfo implements UploadedFileInterface, Stringable
{
    /**
     * @var int[]
     */
    private static array $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    private ?string $tmpFile = null;

    private bool $moved = false;

    /**
     * @var null|string
     */
    private $mimeType;

    /**
     * @param int $size The file size
     * @param int $error The error associated with the uploaded file
     * @param null|string $clientFilename The filename sent by the client
     * @param null|string $clientMediaType The media type sent by the client
     */
    public function __construct(
        string $tmpFile,
        private int $size,
        private int $error,
        private ?string $clientFilename = null,
        private ?string $clientMediaType = null
    ) {
        $this->checkError($this->error);
        $this->isOk() && $this->tmpFile = $tmpFile;
        parent::__construct($tmpFile);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray());
    }

    public function getExtension(): string
    {
        $clientName = $this->getClientFilename();
        $segments = explode('.', $clientName);
        return (string) end($segments);
    }

    public function getMimeType(): string
    {
        if (is_string($this->mimeType)) {
            return $this->mimeType;
        }
        return $this->mimeType = mime_content_type($this->tmpFile);
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return bool True if the file has been uploaded with HTTP and no error occurred
     */
    public function isValid(): bool
    {
        $isOk = $this->error === UPLOAD_ERR_OK;

        return $isOk && is_uploaded_file($this->getPathname());
    }

    /**
     * Determine if the temp file is moved.
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface stream representation of the uploaded file
     * @throws RuntimeException in cases when no stream is available or can be
     *                          created
     */
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('uploaded file is moved');
        }
        return StandardStream::create(fopen($this->tmpFile, 'r+'));
    }

    /**
     * Move the uploaded file to a new location.
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     * The original file or stream MUST be removed on completion.
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath path to which to move the uploaded file
     * @throws InvalidArgumentException if the $targetPath specified is invalid
     * @throws RuntimeException on any error during the move operation, or on
     *                          the second or subsequent call to the method
     */
    public function moveTo($targetPath): void
    {
        $this->validateActive();

        if (! $this->isStringNotEmpty($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided for move operation');
        }

        if ($this->tmpFile) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->tmpFile, $targetPath) : move_uploaded_file($this->tmpFile, $targetPath);
        }

        if (! $this->moved) {
            throw new RuntimeException(sprintf('Uploaded file could not be move to %s', $targetPath));
        }
    }

    /**
     * Retrieve the file size.
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int the file size in bytes or null if unknown
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int one of PHP's UPLOAD_ERR_XXX constants
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return null|string the filename sent by the client or null if none
     *                     was provided
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return null|string the media type sent by the client or null if none
     *                     was provided
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getClientFilename(),
            'type' => $this->getClientMediaType(),
            'tmp_file' => $this->tmpFile,
            'error' => $this->getError(),
            'size' => $this->getSize(),
        ];
    }

    private function checkError(int $error): void
    {
        if (in_array($error, UploadedFile::$errors) === false) {
            throw new InvalidArgumentException('Invalid error status for UploadedFile');
        }
    }

    private function isStringNotEmpty($param): bool
    {
        return is_string($param) && empty($param) === false;
    }

    /**
     * Return true if there is no upload error.
     */
    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @throws RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if ($this->isOk() === false) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }
}
