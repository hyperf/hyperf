<?php
/**
 * @author rock
 * Date: 2020/4/26 11:47
 */

namespace Hyperf\Nsq;


use App\Constants\ResponseCode;
use App\Exception\CommonException;
use GuzzleHttp\Client;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Contract\StdoutLoggerInterface;
class Batch
{
    public $ipList;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var Hyperf\Contract\StdoutLoggerInterface
     */
    private $loger;
    public function __construct(ContainerInterface $container, CacheInterface $cache, ConfigInterface $config,StdoutLoggerInterface $loger)
    {
        $this->container = $container;
        $this->config = $config->get('nsq');
        $this->cache = $cache;
        $this->loger = $loger;
    }


    public function getIpList($nsqlookup)
    {
        $options = [
            'base_uri' => "http://{$nsqlookup['host']}:{$nsqlookup['port']}",
            'timeout' => 2,
            'query' => ['topic' => $nsqlookup['topic']],
        ];

        try {
            $client = make(Client::class, ['config' => $options]);
            $result = $client->get($nsqlookup['url']);
        } catch (\Exception $exception) {
          return  $this->loger->error( 'Nsqlookup did not connect ' . $exception->getMessage());

        }
        $content = $result->getBody()->getContents();
        if (! empty($content)) {
            return $content;
        }

        $this->loger->error( 'nsq is not run or config!');
    }


    public function getNsqIpList($nsqlookup)
    {
        $nsqIpList = $this->cache->get('nsqIpList');
        if (empty($nsqIpList)) {
            $nsqIpList = $this->getIpList($nsqlookup);
            $nsqIpList = $this->handleConfig($nsqIpList);
        } else {
            $nsqIpList = json_decode($nsqIpList, true);
        }

        return $nsqIpList;
    }


    public function handleConfig($object)
    {
        $configArray = [];

        $nsqData = json_decode($object);
        if (! empty($nsqData->producers) && is_array($nsqData->producers)) {
            foreach ($nsqData->producers as $key => $value) {
                $configArray['pool' . $key]['host'] = $value->broadcast_address;
                $configArray['pool' . $key]['port'] = $value->tcp_port;
                $configArray['pool' . $key]['pool'] = $this->config['nsqlookup']['pool'];
            }
            $this->cache->set('nsqIpList', json_encode($configArray), 3600);
            return $configArray;
        }

        $this->loger->error( 'nsq handleConfig is error! ');
    }
}