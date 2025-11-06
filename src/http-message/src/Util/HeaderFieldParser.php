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

namespace Hyperf\HttpMessage\Util;

use RuntimeException;

use function preg_match_all;
use function strcasecmp;
use function strtok;
use function strtolower;
use function substr;

/**
 * Utility class for parsing HTTP header fields.
 *
 * This class provides functionality to parse structured header fields like
 * Content-Type, Content-Disposition, etc., which contain a main value and
 * optional parameters (e.g., "text/html; charset=utf-8").
 *
 * Replaces laminas/laminas-mime dependency with a lightweight implementation.
 */
class HeaderFieldParser
{
	/**
	 * Split a header field into its different parts.
	 *
	 * Parses header fields like "text/html; charset=utf-8; boundary=something"
	 * into structured data.
	 *
	 * @param string $field the header field to parse
	 * @param string|null $wantedPart the wanted part name, or null to return all parts as array
	 * @param string $firstName key name for the first part (default: '0')
	 * @return string|array|null wanted part value, all parts as array, or null if not found
	 */
    public static function splitHeaderField(string $field, ?string $wantedPart = null, string $firstName = '0'): string|array|null
    {
        $wantedPart = strtolower($wantedPart ?? '');
        $firstName = strtolower($firstName);

        // Special case - optimized path for getting just the first part
        if ($firstName === $wantedPart) {
            $field = strtok($field, ';');
            return $field[0] === '"' ? substr($field, 1, -1) : $field;
        }

        // Prepend the first part with firstName as key
        $field = $firstName . '=' . $field;

        // Parse all key=value pairs
        // Pattern: key="quoted value" or key=unquoted-value
        if (! preg_match_all('%([^=\s]+)\s*=\s*("[^"]+"|[^;]+)(;\s*|$)%', $field, $matches)) {
            throw new RuntimeException('not a valid header field');
        }

        // If looking for a specific part
        if ($wantedPart !== '') {
            foreach ($matches[1] as $key => $name) {
                if (strcasecmp($name, $wantedPart) !== 0) {
                    continue;
                }
                // Remove quotes if present
                if ($matches[2][$key][0] !== '"') {
                    return $matches[2][$key];
                }
                return substr($matches[2][$key], 1, -1);
            }
            return null;
        }

        // Return all parts as associative array
        $split = [];
        foreach ($matches[1] as $key => $name) {
            $name = strtolower($name);
            // Remove quotes if present
            if ($matches[2][$key][0] === '"') {
                $split[$name] = substr($matches[2][$key], 1, -1);
            } else {
                $split[$name] = $matches[2][$key];
            }
        }

        return $split;
    }

	/**
	 * Split a Content-Type header into its different parts.
	 *
	 * Convenience method for parsing Content-Type headers.
	 * Returns type and parameters (charset, boundary, etc.).
	 *
	 * @param string $type the content-type header value
	 * @param string|null $wantedPart the wanted part, or null to return all parts
	 * @return string|array|null wanted part or all parts as array('type' => content-type, partname => value)
	 */
    public static function splitContentType(string $type, ?string $wantedPart = null): string|array|null
    {
        return self::splitHeaderField($type, $wantedPart, 'type');
    }
}
