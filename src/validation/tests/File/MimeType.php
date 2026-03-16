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

use Hyperf\Support\MimeTypeExtensionGuesser;

class MimeType
{
    /**
     * The mime types instance.
     */
    private static ?MimeTypeExtensionGuesser $mime = null;

    /**
     * Get the mime types instance.
     */
    public static function getMimeTypes(): MimeTypeExtensionGuesser
    {
        if (self::$mime === null) {
            self::$mime = new MimeTypeExtensionGuesser();
        }

        return self::$mime;
    }

    /**
     * Get the MIME type for a file based on the file's extension.
     */
    public static function from(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return self::get($extension);
    }

    /**
     * Get the MIME type for a given extension or return all mimes.
     */
    public static function get(string $extension): string
    {
        return self::getMimeTypes()->guessMimeType($extension) ?? 'application/octet-stream';
    }

    /**
     * Search for the extension of a given MIME type.
     */
    public static function search(string $mimeType): ?string
    {
        return self::getMimeTypes()->guessExtension($mimeType);
    }
}
