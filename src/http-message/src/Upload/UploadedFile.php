<?php

namespace Hyperf\Http\Message\Upload;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Hyperf\App;

/**
 * @uses      UploadedFile
 * @version   2017-11-04
 * @author    huangzhhui <huangzhwork@gmail.com>
 * @copyright Copyright 2010-2017 Hyperf software
 * @license   PHP Version 7.x {@link http://www.php.net/license/3_0.txt}
 */
class UploadedFile implements UploadedFileInterface
{

    /**
     * @var int[]
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * @var string
     */
    private $clientFilename;

    /**
     * @var string
     */
    private $clientMediaType;

    /**
     * @var int
     */
    private $error;

    /**
     * @var null|string
     */
    private $tmpFile;

    /**
     * @var bool
     */
    private $moved = false;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string      $tmpFile
     * @param int         $size
     * @param int         $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        $tmpFile,
        $size,
        $errorStatus,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->setError($errorStatus)
             ->setSize($size)
             ->setClientFilename($clientFilename)
             ->setClientMediaType($clientMediaType);
        $this->isOk() && $this->setFile($tmpFile);
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param string $file
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setFile($file)
    {
        if (is_string($file)) {
            $this->tmpFile = $file;
        } else {
            throw new \InvalidArgumentException('Invalid stream or file provided for UploadedFile');
        }
        return $this;
    }

    /**
     * @param int $error
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setError($error)
    {
        if (false === is_int($error)) {
            throw new \InvalidArgumentException('Upload file error status must be an integer');
        }

        if (false === in_array($error, UploadedFile::$errors)) {
            throw new \InvalidArgumentException('Invalid error status for UploadedFile');
        }

        $this->error = $error;
        return $this;
    }

    /**
     * @param int $size
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setSize($size)
    {
        if (false === is_int($size)) {
            throw new \InvalidArgumentException('Upload file size must be an integer');
        }

        $this->size = $size;
        return $this;
    }

    /**
     * @param string|null $clientFilename
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setClientFilename($clientFilename)
    {
        if (false === $this->isStringOrNull($clientFilename)) {
            throw new \InvalidArgumentException('Upload file client filename must be a string or null');
        }

        $this->clientFilename = $clientFilename;
        return $this;
    }

    /**
     * @param string|null $clientMediaType
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setClientMediaType($clientMediaType)
    {
        if (false === $this->isStringOrNull($clientMediaType)) {
            throw new \InvalidArgumentException('Upload file client media type must be a string or null');
        }

        $this->clientMediaType = $clientMediaType;
        return $this;
    }

    /**
     * @param mixed $param
     * @return boolean
     */
    private function isStringOrNull($param): bool
    {
        return in_array(gettype($param), ['string', 'NULL']);
    }

    /**
     * @param mixed $param
     * @return boolean
     */
    private function isStringNotEmpty($param): bool
    {
        return is_string($param) && false === empty($param);
    }

    /**
     * Return true if there is no upload error
     *
     * @return boolean
     */
    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @return boolean
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    /**
     * @throws \Hyperf\Exception\RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
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
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        throw new \BadMethodCallException('Not implemented');
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
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *                           the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        $targetPath = App::getAlias($targetPath);
        $this->validateActive();

        if (! $this->isStringNotEmpty($targetPath)) {
            throw new \InvalidArgumentException('Invalid path provided for move operation');
        }

        if ($this->tmpFile) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->tmpFile, $targetPath) : move_uploaded_file($this->tmpFile, $targetPath);
        }

        if (! $this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file could not be move to %s', $targetPath));
        }
    }

    /**
     * Retrieve the file size.
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
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
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
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
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
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
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getClientFilename(),
            'type' => $this->getClientMediaType(),
            'tmp_file' => $this->tmpFile,
            'error' => $this->getError(),
            'size' => $this->getSize(),
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
