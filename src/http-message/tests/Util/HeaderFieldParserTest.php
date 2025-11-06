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

namespace HyperfTest\HttpMessage\Util;

use Hyperf\HttpMessage\Util\HeaderFieldParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(HeaderFieldParser::class)]
class HeaderFieldParserTest extends TestCase
{
    /**
     * Test splitting header field to get all parts.
     */
    #[DataProvider('provideHeaderFieldsForAllParts')]
    public function testSplitHeaderFieldAllParts(string $field, array $expected): void
    {
        $result = HeaderFieldParser::splitHeaderField($field);
		var_dump($result);
        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test splitting header field to get a specific part.
     */
    #[DataProvider('provideHeaderFieldsForSpecificPart')]
    public function testSplitHeaderFieldSpecificPart(string $field, string $wantedPart, ?string $expected, string $firstName = '0'): void
    {
        $result = HeaderFieldParser::splitHeaderField($field, $wantedPart, $firstName);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test splitting Content-Type header.
     */
    #[DataProvider('provideContentTypes')]
    public function testSplitContentType(string $contentType, ?string $wantedPart, string|array|null $expected): void
    {
        $result = HeaderFieldParser::splitContentType($contentType, $wantedPart);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that invalid header field throws exception.
     */
    public function testInvalidHeaderFieldThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not a valid header field');
        HeaderFieldParser::splitHeaderField('');
    }

    /**
     * Test getting the first part only.
     */
    #[DataProvider('provideHeaderFieldsForFirstPart')]
    public function testSplitHeaderFieldFirstPart(string $field, string $expected): void
    {
        $result = HeaderFieldParser::splitHeaderField($field, '0', '0');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test with quoted values containing special characters.
     */
    public function testQuotedValuesWithSpecialCharacters(): void
    {
        $field = 'text/html; charset="utf-8"; name="file; name.txt"';
        $result = HeaderFieldParser::splitHeaderField($field, null, 'type');

        $this->assertEquals([
            'type' => 'text/html',
            'charset' => 'utf-8',
            'name' => 'file; name.txt',
        ], $result);
    }

    /**
     * Test case insensitive parameter names.
     */
    public function testCaseInsensitiveParameterNames(): void
    {
        $field = 'text/html; Charset=utf-8; BOUNDARY=something';
        $result = HeaderFieldParser::splitHeaderField($field, null, 'type');

        $this->assertEquals([
            'type' => 'text/html',
            'charset' => 'utf-8',
            'boundary' => 'something',
        ], $result);
    }

    /**
     * Test retrieving non-existent part returns null.
     */
    public function testNonExistentPartReturnsNull(): void
    {
        $field = 'text/html; charset=utf-8';
        $result = HeaderFieldParser::splitHeaderField($field, 'boundary', 'type');

        $this->assertNull($result);
    }

    /**
     * Test Content-Disposition header.
     */
    public function testContentDispositionHeader(): void
    {
        $field = 'attachment; filename="document.pdf"; size=12345';
        $result = HeaderFieldParser::splitHeaderField($field, null, 'disposition');

        $this->assertEquals([
            'disposition' => 'attachment',
            'filename' => 'document.pdf',
            'size' => '12345',
        ], $result);
    }

    /**
     * Test multipart boundary extraction.
     */
    public function testMultipartBoundaryExtraction(): void
    {
        $field = 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW';
        $boundary = HeaderFieldParser::splitContentType($field, 'boundary');

        $this->assertEquals('----WebKitFormBoundary7MA4YWxkTrZu0gW', $boundary);
    }

    /**
     * Data provider for all parts test.
     */
    public static function provideHeaderFieldsForAllParts(): array
    {
        return [
            'simple content-type' => [
                'text/html',
                ['0' => 'text/html'],
            ],
            'content-type with charset' => [
                'text/html; charset=utf-8',
                ['0' => 'text/html', 'charset' => 'utf-8'],
            ],
            'content-type with multiple params' => [
                'text/html; charset=utf-8; boundary=something',
                ['0' => 'text/html', 'charset' => 'utf-8', 'boundary' => 'something'],
            ],
            'quoted values' => [
                'text/html; charset="utf-8"; name="test.txt"',
                ['0' => 'text/html', 'charset' => 'utf-8', 'name' => 'test.txt'],
            ],
            'multipart with boundary' => [
                'multipart/form-data; boundary=----WebKitFormBoundary',
                ['0' => 'multipart/form-data', 'boundary' => '----WebKitFormBoundary'],
            ],
            'with spaces around equals' => [
                'text/html; charset = utf-8; name = "file.txt"',
                ['0' => 'text/html', 'charset' => 'utf-8', 'name' => 'file.txt'],
            ],
        ];
    }

    /**
     * Data provider for specific part test.
     */
    public static function provideHeaderFieldsForSpecificPart(): array
    {
        return [
            'get charset' => [
                'text/html; charset=utf-8',
                'charset',
                'utf-8',
                '0',
            ],
            'get boundary' => [
                'multipart/form-data; boundary=something',
                'boundary',
                'something',
                '0',
            ],
            'get quoted value' => [
                'text/html; name="test.txt"',
                'name',
                'test.txt',
                '0',
            ],
            'non-existent part' => [
                'text/html; charset=utf-8',
                'boundary',
                null,
                '0',
            ],
            'case insensitive lookup' => [
                'text/html; Charset=utf-8',
                'charset',
                'utf-8',
                '0',
            ],
        ];
    }

    /**
     * Data provider for Content-Type test.
     */
    public static function provideContentTypes(): array
    {
        return [
            'get type only' => [
                'text/html; charset=utf-8',
                'type',
                'text/html',
            ],
            'get charset' => [
                'text/html; charset=utf-8',
                'charset',
                'utf-8',
            ],
            'get all parts' => [
                'text/html; charset=utf-8; boundary=test',
                null,
                ['type' => 'text/html', 'charset' => 'utf-8', 'boundary' => 'test'],
            ],
            'simple type' => [
                'application/json',
                'type',
                'application/json',
            ],
        ];
    }

    /**
     * Data provider for first part test.
     */
    public static function provideHeaderFieldsForFirstPart(): array
    {
        return [
            'simple value' => [
                'text/html; charset=utf-8',
                'text/html',
            ],
            'quoted value' => [
                '"text/html"; charset=utf-8',
                'text/html',
            ],
            'no parameters' => [
                'application/json',
                'application/json',
            ],
        ];
    }
}
