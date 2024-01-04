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
namespace HyperfTest\Stringable;

use Hyperf\Stringable\Str;
use Hyperf\Stringable\StrCache;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class StrTest extends TestCase
{
    public function testCharAt()
    {
        $this->assertEquals('р', Str::charAt('Привет, мир!', 1));
        $this->assertEquals('ち', Str::charAt('「こんにちは世界」', 4));
        $this->assertEquals('w', Str::charAt('Привет, world!', 8));
        $this->assertEquals('界', Str::charAt('「こんにちは世界」', -2));
        $this->assertEquals(null, Str::charAt('「こんにちは世界」', -200));
        $this->assertEquals(null, Str::charAt('Привет, мир!', 100));
    }

    public function testSlug()
    {
        $res = Str::slug('hyperf_', '_');

        $this->assertSame('hyperf', $res);

        $arr = [
            '0' => 0,
            '1' => 1,
            'a' => 'a',
        ];

        $this->assertSame([0, 1, 'a' => 'a'], $arr);
        foreach ($arr as $i => $v) {
            $this->assertIsInt($i);
            break;
        }

        $this->assertSame('hello-world', Str::slug('hello world'));
        $this->assertSame('hello-world', Str::slug('hello-world'));
        $this->assertSame('hello-world', Str::slug('hello_world'));
        $this->assertSame('hello_world', Str::slug('hello_world', '_'));
        $this->assertSame('user-at-host', Str::slug('user@host'));
        $this->assertSame('سلام-دنیا', Str::slug('سلام دنیا', '-', null));
        $this->assertSame('sometext', Str::slug('some text', ''));
        $this->assertSame('', Str::slug('', ''));
        $this->assertSame('', Str::slug(''));
        $this->assertSame('bsm-allah', Str::slug('بسم الله', '-', 'en', ['allh' => 'allah']));
        $this->assertSame('500-dollar-bill', Str::slug('500$ bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500--$----bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500-$-bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500$--bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('500-dollar-bill', Str::slug('500-$--bill', '-', 'en', ['$' => 'dollar']));
        $this->assertSame('أحمد-في-المدرسة', Str::slug('أحمد@المدرسة', '-', null, ['@' => 'في']));
    }

    public function testMask()
    {
        $res = Str::mask('hyperf');

        $this->assertSame('******', $res);

        $res = Str::mask('hyperf', 3);

        $this->assertSame('hyp***', $res);

        $res = Str::mask('hyperf', 3, 1);

        $this->assertSame('hyp*rf', $res);

        $res = Str::mask('hyperf', 0, 3);

        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', 0, 0, '-');

        $this->assertSame('------', $res);

        $res = Str::mask('hyperf', 6, 2);

        $this->assertSame('hyperf', $res);

        $res = Str::mask('hyperf', 7);

        $this->assertSame('hyperf', $res);

        $res = Str::mask('hyperf', 3, 10);

        $this->assertSame('hyp**********', $res);

        $res = Str::mask('hyperf', -3);
        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', -3, 1);
        $this->assertSame('hy*erf', $res);

        $res = Str::mask('hyperf', -3, 3);
        $this->assertSame('***erf', $res);

        $res = Str::mask('hyperf', -3, 5);
        $this->assertSame('*****erf', $res);

        $res = Str::mask('你好啊');

        $this->assertSame('***', $res);

        $res = Str::mask('你好世界', 3);

        $this->assertSame('你好世*', $res);

        $res = Str::mask('你好世界', 2, 1);

        $this->assertSame('你好*界', $res);

        $res = Str::mask('你好世界', 0, 3);

        $this->assertSame('***界', $res);

        $res = Str::mask('你好世界', 1, 1);

        $this->assertSame('你*世界', $res);

        $res = Str::mask('你好世界', 0, 0, '-');

        $this->assertSame('----', $res);

        $res = Str::mask('你好世界', 6, 2);

        $this->assertSame('你好世界', $res);

        $res = Str::mask('你好世界', 7);

        $this->assertSame('你好世界', $res);

        $res = Str::mask('你好世界', 3, 10);

        $this->assertSame('你好世**********', $res);

        $res = Str::mask('你好世界', -1);
        $this->assertSame('***界', $res);

        $res = Str::mask('你好世界', -1, 1);
        $this->assertSame('你好*界', $res);

        $res = Str::mask('你好世界', -3, 3);
        $this->assertSame('***好世界', $res);

        $this->expectException(InvalidArgumentException::class);
        Str::mask('hyperf', -1, -1);
    }

    public function testStartsWith()
    {
        $this->assertFalse(Str::startsWith('hyperf.wiki', 'http://'));
        $this->assertFalse(Str::startsWith('hyperf.wiki', ['http://', 'https://']));
        $this->assertTrue(Str::startsWith('http://www.hyperf.io', 'http://'));
        $this->assertTrue(Str::startsWith('https://www.hyperf.io', ['http://', 'https://']));
        $this->assertFalse(Str::startsWith('Hyperf', ['']));
        $this->assertFalse(Str::startsWith('Hyperf', [null]));
        $this->assertFalse(Str::startsWith('Hyperf', null));
    }

    public function testStripTags()
    {
        $this->assertSame('beforeafter', Str::stripTags('before<br>after'));
        $this->assertSame('before<br>after', Str::stripTags('before<br>after', '<br>'));
        $this->assertSame('before<br>after', Str::stripTags('<strong>before</strong><br>after', '<br>'));
        $this->assertSame('<strong>before</strong><br>after', Str::stripTags('<strong>before</strong><br>after', '<br><strong>'));
        $this->assertSame('<strong>before</strong><br>after', Str::stripTags('<strong>before</strong><br>after', ['<br>', '<strong>']));
        $this->assertSame('beforeafter', Str::stripTags('before<br>after', null));
    }

    public function testPadBoth()
    {
        $this->assertSame('__Alien___', Str::padBoth('Alien', 10, '_'));
        $this->assertSame('  Alien   ', Str::padBoth('Alien', 10));
        $this->assertSame('  ❤MultiByte☆   ', Str::padBoth('❤MultiByte☆', 16));
    }

    public function testPadLeft()
    {
        $this->assertSame('-=-=-Alien', Str::padLeft('Alien', 10, '-='));
        $this->assertSame('     Alien', Str::padLeft('Alien', 10));
        $this->assertSame('     ❤MultiByte☆', Str::padLeft('❤MultiByte☆', 16));
    }

    public function testPadRight()
    {
        $this->assertSame('Alien-----', Str::padRight('Alien', 10, '-'));
        $this->assertSame('Alien     ', Str::padRight('Alien', 10));
        $this->assertSame('❤MultiByte☆     ', Str::padRight('❤MultiByte☆', 16));
    }

    public function testLength()
    {
        $this->assertEquals(11, Str::length('foo bar baz'));
        $this->assertEquals(11, Str::length('foo bar baz', 'UTF-8'));
    }

    public function testUlid()
    {
        $this->assertTrue(Str::isUlid((string) Str::ulid()));
    }

    public function testUuid()
    {
        $this->assertInstanceOf(UuidInterface::class, $uuid = Str::uuid());
        $this->assertTrue(Str::isUuid((string) $uuid));

        $this->assertInstanceOf(UuidInterface::class, $uuid = Str::orderedUuid());
        $this->assertTrue(Str::isUuid((string) $uuid));
    }

    public function testIsMatch()
    {
        $this->assertTrue(Str::isMatch('/.*,.*!/', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/^.*$(.*)/', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/laravel/i', 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch('/^(.*(.*(.*)))/', 'Hello, Laravel!'));

        $this->assertFalse(Str::isMatch('/H.o/', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/^laravel!/i', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/laravel!(.*)/', 'Hello, Laravel!'));
        $this->assertFalse(Str::isMatch('/^[a-zA-Z,!]+$/', 'Hello, Laravel!'));

        $this->assertTrue(Str::isMatch(['/.*,.*!/', '/H.o/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/^laravel!/i', '/^.*$(.*)/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/laravel/i', '/laravel!(.*)/'], 'Hello, Laravel!'));
        $this->assertTrue(Str::isMatch(['/^[a-zA-Z,!]+$/', '/^(.*(.*(.*)))/'], 'Hello, Laravel!'));
    }

    public function testCamel()
    {
        $this->assertSame('helloWorld', Str::camel('HelloWorld'));
        $this->assertSame('helloWorld', Str::camel('hello_world'));
        $this->assertSame('helloWorld', Str::camel('hello-world'));
        $this->assertSame('helloWorld', Str::camel('hello world'));

        $this->assertSame('helloWorld', StrCache::camel('HelloWorld'));
        $this->assertSame('helloWorld', StrCache::camel('HelloWorld'));
        $this->assertSame('helloWorld', StrCache::camel('hello_world'));
        $this->assertSame('helloWorld', StrCache::camel('hello-world'));
        $this->assertSame('helloWorld', StrCache::camel('hello world'));
    }

    public function testSnake()
    {
        $this->assertSame('hello_world', Str::snake('HelloWorld'));
        $this->assertSame('hello_world', Str::snake('hello_world'));
        $this->assertSame('hello_world', Str::snake('hello world'));

        $this->assertSame('hello_world', StrCache::snake('HelloWorld'));
        $this->assertSame('hello_world', StrCache::snake('HelloWorld'));
        $this->assertSame('hello_world', StrCache::snake('hello_world'));
        $this->assertSame('hello_world', StrCache::snake('hello world'));
    }

    public function testStudly()
    {
        $this->assertSame('HelloWorld', Str::studly('helloWorld'));
        $this->assertSame('HelloWorld', Str::studly('hello_world'));
        $this->assertSame('HelloWorld', Str::studly('hello-world'));
        $this->assertSame('HelloWorld', Str::studly('hello world'));
        $this->assertSame('Hello-World', Str::studly('hello world', '-'));

        $this->assertSame('HelloWorld', StrCache::studly('helloWorld'));
        $this->assertSame('HelloWorld', StrCache::studly('helloWorld'));
        $this->assertSame('HelloWorld', StrCache::studly('hello_world'));
        $this->assertSame('HelloWorld', StrCache::studly('hello-world'));
        $this->assertSame('HelloWorld', StrCache::studly('hello world'));
        $this->assertSame('Hello-World', StrCache::studly('hello world', '-'));
    }

    /**
     * @param mixed $validUrl
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('validUrls')]
    public function testValidUrls($url)
    {
        $this->assertTrue(Str::isUrl($url));
    }

    /**
     * @param mixed $invalidUrl
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidUrls')]
    public function testInvalidUrls($url)
    {
        $this->assertFalse(Str::isUrl($url));
    }

    public static function validUrls()
    {
        return [
            ['aaa://fully.qualified.domain/path'],
            ['aaas://fully.qualified.domain/path'],
            ['about://fully.qualified.domain/path'],
            ['acap://fully.qualified.domain/path'],
            ['acct://fully.qualified.domain/path'],
            ['acr://fully.qualified.domain/path'],
            ['adiumxtra://fully.qualified.domain/path'],
            ['afp://fully.qualified.domain/path'],
            ['afs://fully.qualified.domain/path'],
            ['aim://fully.qualified.domain/path'],
            ['apt://fully.qualified.domain/path'],
            ['attachment://fully.qualified.domain/path'],
            ['aw://fully.qualified.domain/path'],
            ['barion://fully.qualified.domain/path'],
            ['beshare://fully.qualified.domain/path'],
            ['bitcoin://fully.qualified.domain/path'],
            ['blob://fully.qualified.domain/path'],
            ['bolo://fully.qualified.domain/path'],
            ['callto://fully.qualified.domain/path'],
            ['cap://fully.qualified.domain/path'],
            ['chrome://fully.qualified.domain/path'],
            ['chrome-extension://fully.qualified.domain/path'],
            ['cid://fully.qualified.domain/path'],
            ['coap://fully.qualified.domain/path'],
            ['coaps://fully.qualified.domain/path'],
            ['com-eventbrite-attendee://fully.qualified.domain/path'],
            ['content://fully.qualified.domain/path'],
            ['crid://fully.qualified.domain/path'],
            ['cvs://fully.qualified.domain/path'],
            ['data://fully.qualified.domain/path'],
            ['dav://fully.qualified.domain/path'],
            ['dict://fully.qualified.domain/path'],
            ['dlna-playcontainer://fully.qualified.domain/path'],
            ['dlna-playsingle://fully.qualified.domain/path'],
            ['dns://fully.qualified.domain/path'],
            ['dntp://fully.qualified.domain/path'],
            ['dtn://fully.qualified.domain/path'],
            ['dvb://fully.qualified.domain/path'],
            ['ed2k://fully.qualified.domain/path'],
            ['example://fully.qualified.domain/path'],
            ['facetime://fully.qualified.domain/path'],
            ['fax://fully.qualified.domain/path'],
            ['feed://fully.qualified.domain/path'],
            ['feedready://fully.qualified.domain/path'],
            ['file://fully.qualified.domain/path'],
            ['filesystem://fully.qualified.domain/path'],
            ['finger://fully.qualified.domain/path'],
            ['fish://fully.qualified.domain/path'],
            ['ftp://fully.qualified.domain/path'],
            ['geo://fully.qualified.domain/path'],
            ['gg://fully.qualified.domain/path'],
            ['git://fully.qualified.domain/path'],
            ['gizmoproject://fully.qualified.domain/path'],
            ['go://fully.qualified.domain/path'],
            ['gopher://fully.qualified.domain/path'],
            ['gtalk://fully.qualified.domain/path'],
            ['h323://fully.qualified.domain/path'],
            ['ham://fully.qualified.domain/path'],
            ['hcp://fully.qualified.domain/path'],
            ['http://fully.qualified.domain/path'],
            ['https://fully.qualified.domain/path'],
            ['iax://fully.qualified.domain/path'],
            ['icap://fully.qualified.domain/path'],
            ['icon://fully.qualified.domain/path'],
            ['im://fully.qualified.domain/path'],
            ['imap://fully.qualified.domain/path'],
            ['info://fully.qualified.domain/path'],
            ['iotdisco://fully.qualified.domain/path'],
            ['ipn://fully.qualified.domain/path'],
            ['ipp://fully.qualified.domain/path'],
            ['ipps://fully.qualified.domain/path'],
            ['irc://fully.qualified.domain/path'],
            ['irc6://fully.qualified.domain/path'],
            ['ircs://fully.qualified.domain/path'],
            ['iris://fully.qualified.domain/path'],
            ['iris.beep://fully.qualified.domain/path'],
            ['iris.lwz://fully.qualified.domain/path'],
            ['iris.xpc://fully.qualified.domain/path'],
            ['iris.xpcs://fully.qualified.domain/path'],
            ['itms://fully.qualified.domain/path'],
            ['jabber://fully.qualified.domain/path'],
            ['jar://fully.qualified.domain/path'],
            ['jms://fully.qualified.domain/path'],
            ['keyparc://fully.qualified.domain/path'],
            ['lastfm://fully.qualified.domain/path'],
            ['ldap://fully.qualified.domain/path'],
            ['ldaps://fully.qualified.domain/path'],
            ['magnet://fully.qualified.domain/path'],
            ['mailserver://fully.qualified.domain/path'],
            ['mailto://fully.qualified.domain/path'],
            ['maps://fully.qualified.domain/path'],
            ['market://fully.qualified.domain/path'],
            ['message://fully.qualified.domain/path'],
            ['mid://fully.qualified.domain/path'],
            ['mms://fully.qualified.domain/path'],
            ['modem://fully.qualified.domain/path'],
            ['ms-help://fully.qualified.domain/path'],
            ['ms-settings://fully.qualified.domain/path'],
            ['ms-settings-airplanemode://fully.qualified.domain/path'],
            ['ms-settings-bluetooth://fully.qualified.domain/path'],
            ['ms-settings-camera://fully.qualified.domain/path'],
            ['ms-settings-cellular://fully.qualified.domain/path'],
            ['ms-settings-cloudstorage://fully.qualified.domain/path'],
            ['ms-settings-emailandaccounts://fully.qualified.domain/path'],
            ['ms-settings-language://fully.qualified.domain/path'],
            ['ms-settings-location://fully.qualified.domain/path'],
            ['ms-settings-lock://fully.qualified.domain/path'],
            ['ms-settings-nfctransactions://fully.qualified.domain/path'],
            ['ms-settings-notifications://fully.qualified.domain/path'],
            ['ms-settings-power://fully.qualified.domain/path'],
            ['ms-settings-privacy://fully.qualified.domain/path'],
            ['ms-settings-proximity://fully.qualified.domain/path'],
            ['ms-settings-screenrotation://fully.qualified.domain/path'],
            ['ms-settings-wifi://fully.qualified.domain/path'],
            ['ms-settings-workplace://fully.qualified.domain/path'],
            ['msnim://fully.qualified.domain/path'],
            ['msrp://fully.qualified.domain/path'],
            ['msrps://fully.qualified.domain/path'],
            ['mtqp://fully.qualified.domain/path'],
            ['mumble://fully.qualified.domain/path'],
            ['mupdate://fully.qualified.domain/path'],
            ['mvn://fully.qualified.domain/path'],
            ['news://fully.qualified.domain/path'],
            ['nfs://fully.qualified.domain/path'],
            ['ni://fully.qualified.domain/path'],
            ['nih://fully.qualified.domain/path'],
            ['nntp://fully.qualified.domain/path'],
            ['notes://fully.qualified.domain/path'],
            ['oid://fully.qualified.domain/path'],
            ['opaquelocktoken://fully.qualified.domain/path'],
            ['pack://fully.qualified.domain/path'],
            ['palm://fully.qualified.domain/path'],
            ['paparazzi://fully.qualified.domain/path'],
            ['pkcs11://fully.qualified.domain/path'],
            ['platform://fully.qualified.domain/path'],
            ['pop://fully.qualified.domain/path'],
            ['pres://fully.qualified.domain/path'],
            ['prospero://fully.qualified.domain/path'],
            ['proxy://fully.qualified.domain/path'],
            ['psyc://fully.qualified.domain/path'],
            ['query://fully.qualified.domain/path'],
            ['redis://fully.qualified.domain/path'],
            ['rediss://fully.qualified.domain/path'],
            ['reload://fully.qualified.domain/path'],
            ['res://fully.qualified.domain/path'],
            ['resource://fully.qualified.domain/path'],
            ['rmi://fully.qualified.domain/path'],
            ['rsync://fully.qualified.domain/path'],
            ['rtmfp://fully.qualified.domain/path'],
            ['rtmp://fully.qualified.domain/path'],
            ['rtsp://fully.qualified.domain/path'],
            ['rtsps://fully.qualified.domain/path'],
            ['rtspu://fully.qualified.domain/path'],
            ['s3://fully.qualified.domain/path'],
            ['secondlife://fully.qualified.domain/path'],
            ['service://fully.qualified.domain/path'],
            ['session://fully.qualified.domain/path'],
            ['sftp://fully.qualified.domain/path'],
            ['sgn://fully.qualified.domain/path'],
            ['shttp://fully.qualified.domain/path'],
            ['sieve://fully.qualified.domain/path'],
            ['sip://fully.qualified.domain/path'],
            ['sips://fully.qualified.domain/path'],
            ['skype://fully.qualified.domain/path'],
            ['smb://fully.qualified.domain/path'],
            ['sms://fully.qualified.domain/path'],
            ['smtp://fully.qualified.domain/path'],
            ['snews://fully.qualified.domain/path'],
            ['snmp://fully.qualified.domain/path'],
            ['soap.beep://fully.qualified.domain/path'],
            ['soap.beeps://fully.qualified.domain/path'],
            ['soldat://fully.qualified.domain/path'],
            ['spotify://fully.qualified.domain/path'],
            ['ssh://fully.qualified.domain/path'],
            ['steam://fully.qualified.domain/path'],
            ['stun://fully.qualified.domain/path'],
            ['stuns://fully.qualified.domain/path'],
            ['submit://fully.qualified.domain/path'],
            ['svn://fully.qualified.domain/path'],
            ['tag://fully.qualified.domain/path'],
            ['teamspeak://fully.qualified.domain/path'],
            ['tel://fully.qualified.domain/path'],
            ['teliaeid://fully.qualified.domain/path'],
            ['telnet://fully.qualified.domain/path'],
            ['tftp://fully.qualified.domain/path'],
            ['things://fully.qualified.domain/path'],
            ['thismessage://fully.qualified.domain/path'],
            ['tip://fully.qualified.domain/path'],
            ['tn3270://fully.qualified.domain/path'],
            ['turn://fully.qualified.domain/path'],
            ['turns://fully.qualified.domain/path'],
            ['tv://fully.qualified.domain/path'],
            ['udp://fully.qualified.domain/path'],
            ['unreal://fully.qualified.domain/path'],
            ['urn://fully.qualified.domain/path'],
            ['ut2004://fully.qualified.domain/path'],
            ['vemmi://fully.qualified.domain/path'],
            ['ventrilo://fully.qualified.domain/path'],
            ['videotex://fully.qualified.domain/path'],
            ['view-source://fully.qualified.domain/path'],
            ['wais://fully.qualified.domain/path'],
            ['webcal://fully.qualified.domain/path'],
            ['ws://fully.qualified.domain/path'],
            ['wss://fully.qualified.domain/path'],
            ['wtai://fully.qualified.domain/path'],
            ['wyciwyg://fully.qualified.domain/path'],
            ['xcon://fully.qualified.domain/path'],
            ['xcon-userid://fully.qualified.domain/path'],
            ['xfire://fully.qualified.domain/path'],
            ['xmlrpc.beep://fully.qualified.domain/path'],
            ['xmlrpc.beeps://fully.qualified.domain/path'],
            ['xmpp://fully.qualified.domain/path'],
            ['xri://fully.qualified.domain/path'],
            ['ymsgr://fully.qualified.domain/path'],
            ['z39.50://fully.qualified.domain/path'],
            ['z39.50r://fully.qualified.domain/path'],
            ['z39.50s://fully.qualified.domain/path'],
            ['http://a.pl'],
            ['http://localhost/url.php'],
            ['http://local.dev'],
            ['http://google.com'],
            ['http://goog_le.com'],
            ['http://www.google.com'],
            ['https://google.com'],
            ['http://illuminate.dev'],
            ['http://localhost'],
            ['https://laravel.com/?'],
            ['http://президент.рф/'],
            ['http://스타벅스코리아.com'],
            ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
            ['https://laravel.com?'],
            ['https://laravel.com?q=1'],
            ['https://laravel.com/?q=1'],
            ['https://laravel.com#'],
            ['https://laravel.com#fragment'],
            ['https://laravel.com/#fragment'],
        ];
    }

    public static function invalidUrls()
    {
        return [
            ['aslsdlks'],
            ['google.com'],
            ['://google.com'],
            ['http ://google.com'],
            ['http:/google.com'],
            ['http://google.com::aa'],
            ['http://google.com:aa'],
            ['http://127.0.0.1:aa'],
            ['http://[::1'],
            ['foo://bar'],
            ['javascript://test%0Aalert(321)'],
            ['example.com'],
            ['://example.com'],
            ['http ://example.com'],
            ['http:/example.com'],
            ['http://example.com::aa'],
            ['http://example.com:aa'],
            ['faked://example.fr'],
            ['http://127.0.0.1:aa/'],
            ['http://[::1'],
            ['http://☎'],
            ['http://☎.'],
            ['http://☎/'],
            ['http://☎/path'],
            ['http://hello.☎'],
            ['http://hello.☎.'],
            ['http://hello.☎/'],
            ['http://hello.☎/path'],
            ['http://:password@symfony.com'],
            ['http://:password@@symfony.com'],
            ['http://username:passwordsymfony.com'],
            ['http://usern@me:password@symfony.com'],
            ['http://nota%hex:password@symfony.com'],
            ['http://username:nota%hex@symfony.com'],
            ['http://example.com/exploit.html?<script>alert(1);</script>'],
            ['http://example.com/exploit.html?hel lo'],
            ['http://example.com/exploit.html?not_a%hex'],
            ['http://'],
            ['http://example.co-'],
            ['http://example.co-/path'],
            ['http:///path'],
        ];
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('Hyperf', ['h'], true));
        $this->assertTrue(Str::contains('Hyperf', ['H']));
        $this->assertFalse(Str::contains('Hyperf', ['']));
        $this->assertFalse(Str::contains('Hyperf', [null]));
        $this->assertFalse(Str::contains('Hyperf', null));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('Hyperf', ['f']));
        $this->assertFalse(Str::endsWith('Hyperf', ['']));
        $this->assertFalse(Str::endsWith('Hyperf', [null]));
        $this->assertFalse(Str::endsWith('Hyperf', null));
    }

    public function testContainsAll()
    {
        $this->assertTrue(Str::containsAll('Hyperf', ['h'], true));
        $this->assertFalse(Str::containsAll('Hyperf', ['h']));
    }

    public function testIsUrl()
    {
        $this->assertTrue(Str::isUrl('https://baidu.com'));
        $this->assertFalse(Str::isUrl('invalid url'));
    }

    public function testStrBetweenFirst()
    {
        $data = [
            ['abc', ['abc', '', 'c']],
            ['abc', ['abc', 'a', '']],
            ['abc', ['abc', '', '']],
            ['b', ['abc', 'a', 'c']],
            ['b', ['dddabc', 'a', 'c']],
            ['b', ['abcddd', 'a', 'c']],
            ['b', ['dddabcddd', 'a', 'c']],
            ['nn', ['hannah', 'ha', 'ah']],
            ['a', ['[a]ab[b]', '[', ']']],
            ['foo', ['foofoobar', 'foo', 'bar']],
            ['', ['foobarbar', 'foo', 'bar']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::betweenFirst(...$item[1]));
        }
    }

    public function testExcerpt()
    {
        $this->assertSame('...is a beautiful morn...', Str::excerpt('This is a beautiful morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('This is a...', Str::excerpt('This is a beautiful morning', 'this', ['radius' => 5]));
        $this->assertSame('...iful morning', Str::excerpt('This is a beautiful morning', 'morning', ['radius' => 5]));
        $this->assertNull(Str::excerpt('This is a beautiful morning', 'day'));
        $this->assertSame('...is a beautiful! mor...', Str::excerpt('This is a beautiful! morning', 'Beautiful', ['radius' => 5]));
        $this->assertSame('...is a beautiful? mor...', Str::excerpt('This is a beautiful? morning', 'beautiful', ['radius' => 5]));
        $this->assertSame('', Str::excerpt('', '', ['radius' => 0]));
        $this->assertSame('a', Str::excerpt('a', 'a', ['radius' => 0]));
        // $this->assertSame('...b...', Str::excerpt('abc', 'B', ['radius' => 0]));
        $this->assertSame('abc', Str::excerpt('abc', 'b', ['radius' => 1]));
        $this->assertSame('abc...', Str::excerpt('abcd', 'b', ['radius' => 1]));
        $this->assertSame('...abc', Str::excerpt('zabc', 'b', ['radius' => 1]));
        $this->assertSame('...abc...', Str::excerpt('zabcd', 'b', ['radius' => 1]));
        $this->assertSame('zabcd', Str::excerpt('zabcd', 'b', ['radius' => 2]));
        $this->assertSame('zabcd', Str::excerpt('  zabcd  ', 'b', ['radius' => 4]));
        $this->assertSame('...abc...', Str::excerpt('z  abc  d', 'b', ['radius' => 1]));
        $this->assertSame('[...]is a beautiful morn[...]', Str::excerpt('This is a beautiful morning', 'beautiful', ['omission' => '[...]', 'radius' => 5]));
        $this->assertSame(
            'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome tempera[...]',
            Str::excerpt(
                'This is the ultimate supercalifragilisticexpialidoceous very looooooooooooooooooong looooooooooooong beautiful morning with amazing sunshine and awesome temperatures. So what are you gonna do about it?',
                'very',
                ['omission' => '[...]'],
            )
        );

        $this->assertSame('...y...', Str::excerpt('taylor', 'y', ['radius' => 0]));
        $this->assertSame('...ayl...', Str::excerpt('taylor', 'Y', ['radius' => 1]));
        $this->assertSame('<div> The article description </div>', Str::excerpt('<div> The article description </div>', 'article'));
        $this->assertSame('...The article desc...', Str::excerpt('<div> The article description </div>', 'article', ['radius' => 5]));
        $this->assertSame('The article description', Str::excerpt(strip_tags('<div> The article description </div>'), 'article'));
        $this->assertSame('', Str::excerpt(null));
        $this->assertSame('', Str::excerpt(''));
        $this->assertSame('', Str::excerpt(null));
        $this->assertSame('T...', Str::excerpt('The article description', null, ['radius' => 1]));
        $this->assertSame('The arti...', Str::excerpt('The article description', '', ['radius' => 8]));
        $this->assertSame('', Str::excerpt(' '));
        $this->assertSame('The arti...', Str::excerpt('The article description', ' ', ['radius' => 4]));
        $this->assertSame('...cle description', Str::excerpt('The article description', 'description', ['radius' => 4]));
        $this->assertSame('T...', Str::excerpt('The article description', 'T', ['radius' => 0]));
        $this->assertSame('What i?', Str::excerpt('What is the article?', 'What', ['radius' => 2, 'omission' => '?']));

        $this->assertSame('...ö - 二 sān 大åè...', Str::excerpt('åèö - 二 sān 大åèö', '二 sān', ['radius' => 4]));
        $this->assertSame('åèö - 二...', Str::excerpt('åèö - 二 sān 大åèö', 'åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('åèö - 二 sān 大åèö', Str::excerpt('åèö - 二 sān 大åèö', 'åèö - 二 sān 大åèö', ['radius' => 4]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...༼...', Str::excerpt('㏗༼㏗', '༼', ['radius' => 0]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'ê', ['radius' => 2]));
        $this->assertSame('...ocê e...', Str::excerpt('Como você está', 'Ê', ['radius' => 2]));
        $this->assertSame('João...', Str::excerpt('João Antônio ', 'jo', ['radius' => 2]));
        $this->assertSame('João Antô...', Str::excerpt('João Antônio', 'JOÃO', ['radius' => 5]));
    }

    public function testIsJson()
    {
        $data = [
            [true, '1'],
            [true, '[1,2,3]'],
            [true, '[1,   2,   3]'],
            [true, '{"first": "John", "last": "Doe"}'],
            [true, '[{"first": "John", "last": "Doe"}, {"first": "Jane", "last": "Doe"}]'],
            [false, '1,'],
            [false, '[1,2,3'],
            [false, '[1,   2   3]'],
            [false, '{first: "John"}'],
            [false, '[{first: "John"}, {first: "Jane"}]'],
            [false, ''],
            [false, null],
            [false, []],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::isJson($item[1]));
        }
    }

    public function testLcfirst()
    {
        $data = [
            ['laravel', 'Laravel'],
            ['laravel framework', 'Laravel framework'],
            ['мама', 'Мама'],
            ['мама мыла раму', 'Мама мыла раму'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::lcfirst($item[1]));
        }
    }

    public function testUcsplit()
    {
        $data = [
            [['Laravel_p_h_p_framework'], 'Laravel_p_h_p_framework'],
            [['Laravel_', 'P_h_p_framework'], 'Laravel_P_h_p_framework'],
            [['laravel', 'P', 'H', 'P', 'Framework'], 'laravelPHPFramework'],
            [['Laravel-ph', 'P-framework'], 'Laravel-phP-framework'],
            [['Żółta', 'Łódka'], 'ŻółtaŁódka'],
            [['sind', 'Öde', 'Und', 'So'], 'sindÖdeUndSo'],
            [['Öffentliche', 'Überraschungen'], 'ÖffentlicheÜberraschungen'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::ucsplit($item[1]));
        }
    }

    public function testIsUuidWithValidUuid()
    {
        $this->assertTrue(Str::isUuid(Str::uuid()->__toString()));
    }

    public function testIsUuidWithInvalidUuid()
    {
        $this->assertFalse(Str::isUuid('foo'));
    }

    public function testWordCount()
    {
        $data = [
            [2, 'Hello, world!'],
            [10, 'Hi, this is my first contribution to the Laravel framework.'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::wordCount($item[1]));
        }
    }

    public function testPassword()
    {
        $data = [
            [32, []],
            [10, [10]],
        ];
        foreach ($data as $item) {
            $this->assertIsString(Str::password(...$item[1]));
            $this->assertSame($item[0], strlen(Str::password(...$item[1])));
        }
    }

    public function testReplaceStart()
    {
        $data = [
            ['foobar foobar', ['bar', 'qux', 'foobar foobar']],
            ['foo/bar? foo/bar?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['quxbar foobar', ['foo', 'qux', 'foobar foobar']],
            ['qux? foo/bar?', ['foo/bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['bar foobar', ['foo', '', 'foobar foobar']],
            ['1', [0, '1', '0']],
            ['xxxnköping Malmö', ['Jö', 'xxx', 'Jönköping Malmö']],
            ['Jönköping Malmö', ['', 'yyy', 'Jönköping Malmö']],
        ];

        foreach ($data as $item) {
            $this->assertSame($item[0], Str::replaceStart(...$item[1]));
        }
    }

    public function testReplaceEnd()
    {
        $data = [
            ['foobar fooqux', ['bar', 'qux', 'foobar foobar']],
            ['foo/bar? foo/qux?', ['bar?', 'qux?', 'foo/bar? foo/bar?']],
            ['foobar foo', ['bar', '', 'foobar foobar']],
            ['foobar foobar', ['xxx', 'yyy', 'foobar foobar']],
            ['foobar foobar', ['', 'yyy', 'foobar foobar']],
            ['fooxxx foobar', ['xxx', 'yyy', 'fooxxx foobar']],
            ['Malmö Jönköping', ['ö', 'xxx', 'Malmö Jönköping']],
            ['Malmö Jönkyyy', ['öping', 'yyy', 'Malmö Jönköping']],
        ];

        foreach ($data as $item) {
            $this->assertSame($item[0], Str::replaceEnd(...$item[1]));
        }
    }

    public function testReverse()
    {
        $data = [
            ['FooBar', 'raBooF'],
            ['Teniszütő', 'őtüzsineT'],
            ['❤MultiByte☆', '☆etyBitluM❤'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::reverse($item[1]));
        }
    }

    public function testSquish()
    {
        $data = [
            ['laravel php framework', ' laravel   php  framework '],
            ['laravel php framework', "laravel\t\tphp\n\nframework"],
            [
                'laravel php framework', '
            laravel
            php
            framework
        ',
            ],
            ['laravel php framework', 'laravelㅤㅤㅤphpㅤframework'],
            ['laravel php framework', 'laravelᅠᅠᅠᅠᅠᅠᅠᅠᅠᅠphpᅠᅠframework'],
            ['laravel php framework', '   laravel   php   framework   '],
            ['123', '   123    '],
            ['だ', 'だ'],
            ['ム', 'ム'],
            ['だ', '   だ    '],
            ['ム', '   ム    '],
            ['ム', '﻿   ム ﻿﻿   ﻿'],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::squish($item[1]));
        }
    }

    public function testSubstrReplace()
    {
        $this->assertSame('12:00', Str::substrReplace('1200', ':', 2, 0));
        $this->assertSame('The Laravel Framework', Str::substrReplace('The Framework', 'Laravel ', 4, 0));
        $this->assertSame('Laravel – The PHP Framework for Web Artisans', Str::substrReplace('Laravel Framework', '– The PHP Framework for Web Artisans', 8));
    }

    public function testSwapKeywords()
    {
        $this->assertSame(
            'PHP 8 is fantastic',
            Str::swap([
                'PHP' => 'PHP 8',
                'awesome' => 'fantastic',
            ], 'PHP is awesome')
        );

        $this->assertSame(
            'foo bar baz',
            Str::swap([
                'ⓐⓑ' => 'baz',
            ], 'foo bar ⓐⓑ')
        );
    }

    public function testWrap()
    {
        $this->assertEquals('"value"', Str::wrap('value', '"'));
        $this->assertEquals('foo-bar-baz', Str::wrap('-bar-', 'foo', 'baz'));
    }

    public function testWordWrap()
    {
        $data = [
            ['Hello<br />World', ['Hello World', 3, '<br />']],
            ['Hel<br />lo<br />Wor<br />ld', ['Hello World', 3, '<br />', true]],
            ['❤Multi<br />Byte☆❤☆❤☆❤', ['❤Multi Byte☆❤☆❤☆❤', 3, '<br />']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::wordWrap(...$item[1]));
        }
    }

    public function testConvertCase()
    {
        $data = [
            ['MARY HAD A LITTLE LAMB AND SHE LOVED IT SO', ['mary had a Little lamb and she loved it so', MB_CASE_UPPER, 'UTF-8']],
            ['Mary Had A Little Lamb And She Loved It So', ['mary had a Little lamb and she loved it so', MB_CASE_TITLE, 'UTF-8']],
        ];
        foreach ($data as $item) {
            $this->assertSame($item[0], Str::convertCase(...$item[1]));
        }
    }

    public function testReplaceLast()
    {
        $this->assertSame('Hello earth', Str::replaceLast('world', 'earth', 'Hello world'));
        $this->assertSame('Hello world', Str::replaceLast('', 'earth', 'Hello world'));
    }

    public function testReplaceMatches()
    {
        $this->assertSame('http://hyperf.io', Str::replaceMatches('/^https:\/\//', 'http://', 'https://hyperf.io'));
        $this->assertSame('http://hyperf.io', Str::replaceMatches('/^https:\/\//', fn ($matches) => 'http://', 'https://hyperf.io'));
    }
}
