<?php
declare(strict_types=1);
/**
 * @author   crayxn <https://github.com/crayxn>
 * @contact  crayxn@qq.com
 */

namespace HyperfTest\Nacos\Cases\Sign;

use GuzzleHttp\Psr7\Response;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use HyperfTest\Nacos\AbstractTestCase;
use Psr\Http\Message\RequestInterface;

class NacosNamingSignTest extends AbstractTestCase
{
    public function testNacosNamingSign()
    {
        $accessKey = "ak";
        $accessSecret = "as";
        $serverName = "example.rpc";
        $headers = [];
        $application = new Application(new Config([
            'access_key' => $accessKey,
            'access_secret' => $accessSecret,
            'guzzle_config' => [
                'handler' => function (RequestInterface $request) use (&$headers) {
                    $headers = $request->getHeaders();
                    return new Response();
                }
            ],
        ]));
        $application->service->detail($serverName);
        [, $name] = explode("@@", $headers['data'][0] ?? '');
        //check accessKey
        $this->assertSame($headers['ak'][0] ?? '', $accessKey);
        //check serverName
        $this->assertSame($serverName, $name);
        //check signature
        $this->assertSame(base64_encode(hash_hmac('sha1', $headers['data'][0] ?? '', $accessSecret, true)), $headers['signature'][0] ?? '');
    }
}