# Redis

## 安装

```
composer require hyperf/redis
```

## 配置

| 配置项 |  类型   |   默认值    |   备注    |
|:------:|:-------:|:-----------:|:---------:|
|  host  | string  | 'localhost' | Redis地址 |
|  auth  | string  |     无      |   密码    |
|  port  | integer |    6379     |   端口    |
|   db   | integer |      0      |    DB     |

```php
<?php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

## 使用

`hyperf/redis` 实现了 `ext-redis` 代理和连接池，用户可以直接通过依赖注入容器注入 `\Redis` 来使用 Redis 客户端，实际获得的是 `Hyperf\Redis\Redis` 的一个代理对象。

```php
<?php
use Hyperf\Utils\ApplicationContext;

$container = ApplicationContext::getContainer();

$redis = $container->get(\Redis::class);
$result = $redis->keys('*');
```

## 多库配置

有时候在实际使用中，一个 `Redis` 库并不满足需求，一个项目往往需要配置多个库，这个时候，我们就需要修改一下配置文件 `redis.php`，如下：

```php
<?php

return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
    // 增加一个名为 foo 的 Redis 连接池
    'foo' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', ''),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => 1,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

### 通过代理类使用

我们可以重写一个 `FooRedis` 类并继承 `Hyperf\Redis\Redis` 类，修改 `poolName` 为上述的 `foo`，即可完成对连接池的切换，示例：

```php
<?php
use Hyperf\Redis\Redis;

class FooRedis extends Redis
{
    // 对应的 Pool 的 key 值
    protected $poolName = 'foo';
}

// 通过 DI 容器获取或直接注入当前类
$redis = $this->container->get(FooRedis::class);

$result = $redis->keys('*');

```

### 使用工厂类

在每个库对应一个固定的使用场景时，通过代理类是一种很好的区分的方法，但有时候需求可能会更加的动态，这时候我们可以通过 `Hyperf\Redis\RedisFactory` 工厂类来动态的传递 `poolName` 来获得对应的连接池的客户端，而无需为每个库创建代理类，示例如下：

```php
<?php
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\ApplicationContext;

$container = ApplicationContext::getContainer();

// 通过 DI 容器获取或直接注入 RedisFactory 类
$redis = $container->get(RedisFactory::class)->get('foo');
$result = $redis->keys('*');
```

## 附 phpRedis 常用Api函数列表  
```phpRedis
# 基本的键值操作  
$redis->keys('*');                                            //取出所有的键名 
$redis->setex('key',$value,$second);                          //设置键 key = $value，有效期为 $second 秒, [true]
$redis->set('key',$value);                                    //设置 key = $value, [true]
$redis->get('key');                                           //获取 key, [value]
$redis->mset(array('key'=>'value1','key2'=>'value2'));        //设置一个或多个键值, [true]
$redis->mget(array('key','key2'));                            //返回所查询键的值, [array]
$redis->setnx('key','value');                                 //键不存在时设置值，存在键则无效
$redis->strlen('key');                                        //获取键 key 对应值的长度
$redis->incr('key');                                          //自增1，如不存在 key ,赋值为1，返回最终值
$redis->decr('key');                                          //自减1，如不存在 key ,赋值为-1，返回最终值
$redis->incrby('key',$num);                                   //自增 $num, 不存在为赋值
$redis->decrby('key',$num);                                   //自减 $num, 不存在则赋值为 -$sum
$redis->append('key','string');                               //把string追加到 key 现有的value中 [追加后的个数]
$redis->del('key');                                           //删除 key，[del_num]
$redis->del(array('key1','key2','key3'));                     //删除 key,[del_num]
$redis->getset('key','new_value');                            //先获得 key 的旧值，然后重新赋值,[ old_value ]

# 列表List操作  
$redis->lPush('L_key','value');                               // 将 value 添加至列表 L_key 的左侧, [列表元素总个数] 
$redis->rPop('L_key');                                        // 从列表 L_key 的右侧弹出一个元素, [最右侧元素值]  
$redis->rPush('L_key','value');                               // 将 value 添加至列表 L_key 的右侧, [列表元素总个数]  
$redis->lPop('L_key');                                        // 从列表 L_key 的左侧弹出一个元素, [最左侧元素值]  
$redis->lLen('L_key');                                        // 返回列表 L_key 的个数，不存在 L_key ,返回 0  
$redis->lIndex('L_key',index);                                // 返回列表 L_key 索引为 index 的值  
$redis->lSet('L_key',index,new_value);                        // 赋值列表 L_key 索引为 index 的的元素值为 new_value, 成功返回 1   
$redis->lRem('L_key','del_value',n);                          // 移除列表 L_key 中，值为 del_value 的元素,第三个参数 n, 0表示全部, >0 表示从列表头开始删除 n 个, <0 反向删除 n 的绝对值个元素,成功返回 1   

# Hash列表操作  
$redis->hSet('H_key','key','value');                          // 设置主键 H_key 下，子键 key 的值为 value, [1]  
$redis->hGet('H_key','key');                                  // 获取主键 H_key 下，子键 key 的值, [相关值]    
$redis->hMSet('H_key',array('key1'=>'value1','key2'=>'v2'));  // 批量设置主键 H_key 下，子键值对的值, [true]    
$redis->hMGet('H_key',array('key1','key2'));                  // 批量获取主键 H_key 下，子键值 key1、key2的值, [array]       
$redis->hKey('H_key');                                        // 获取主键 H_key 下，全部的子键, [array]       
$redis->hExists('H_key','key1');                              // 判断主键 H_key 下，子键 key1 是否存在, [true]       
$redis->hGetAll('H_key');                                     // 判断主键 H_key 下，全部子键值对, [array]       
$redis->hDel('H_key','key1','key2');                          // 删除主键 H_key 下，子键 key1、key2, [陈宫删除子键的个数]       

# 集合Set操作,集合中元素不能重复    
$redis->sAdd('S_key','value1','value2');                      // 给集合键 S_key 添加成员值 value1、value2, 重复元素不添加 [成功添加的个数]       
$redis->sCard('S_key');                                       // 获取集合键 S_key 成员数目, [成员总数目]       
$redis->sMembers('S_key');                                    // 获取集合键 S_key 的全部成员, [array]       
$redis->sIsMember('S_key','value1');                          // 判断集合键 S_key 中全部成员值 value1 是否存在, [true]       
$redis->sPop('S_key');                                        // 随机删除集合键 S_key 中的一个成员值,, [删除的成员值]            
$redis->sRandMember('S_key',num);                             // 随机获取集合键 S_key 中的 num 个成员值,, [array]       
$redis->sinter('S_key1','S_key2');                            // 获取集合键 S_key1、S_key2 的交集, [array]  
$redis->sunion('S_key1','S_key2');                            // 获取集合键 S_key1、S_key2 的并集, [array]  
$redis->sdiff('S_key1','S_key2');                             // 获取集合键 S_key1、S_key2 的差集（S_key1中有，S_key2中没有的成员）, [array]  
$redis->sMove('S_key1','S_key2','value1');                    // 将集合键 S_key1 中 的成员 value1, 移动到S_key2中, [true]

# 有序集合ZSet操作,有序集合中元素不能重复   
$redis->zAdd('Z_key',$score1,$member1,$scoreN,$memberN);      //将一个或多个member元素及其score值加入到有序集 Z_key 当中, [num]
$redis->zrem('Z_key','member1','membern');                    //删，移除有序集 Z_key 中的一个或多个成员，不存在的成员将被忽略, [del_num]
$redis->zrange('Z_key',startScore,stopScore);;                //通过 score 从小到大拿member值, [array]
$redis->zrevrange('Z_key',startScore,stopScore);;             //通过 score 从大到小拿member值, [array]
$redis->zrank('Z_key','member1');;                            //获取集合键 Z_key 中成员 member1 的排名（按照分数从低到高）, [排名序号,从 0 开始]
$redis->zrevrank('Z_key','member1');;                         //获取集合键 Z_key 中成员 member1 的排名（按照分数从高到低）, [排名序号,从 0 开始]
$redis->zcard('Z_key');;                                      //获取集合键 Z_key 中成员数量, [成员个数]
$redis->zcount('Z_key',min,max);                              //获取集合键 Z_key 中成员权重分在min，max闭区间范围内的数量, [成员个数]
$redis->ZINTERSTORE();                                        //交集
$redis->ZUNIONSTORE();                                        //差集

# 其他命令  
$redis->auth('password');                                    //登录数据库 [true]
$redis->select(db_index);                                    //按照索引选择数据库, 索引从 0 开始 [true]
$redis->expire('key',10);                                    //设置 key 失效时间为10秒之后 [true]
$redis->ttl('key');                                          //查看 key 的剩余有效时间，单位：秒, [剩余有效秒数]
$redis->flushDB();                                           //清空当前数据库所有数据   [true]
$redis->flushAll();                                          //清空所有数据库数据   [true]
$redis->move('key',db_index);                                //移动 key 到索引为 db_index 的数据库，索引从 0 开始  [true]
$redis->save();                                              //当前数据库数据保存在硬盘    [true]
$redis->bgsave();                                            //当前数据库数据异步保存在硬盘 [true]
$redis->info();                                              //查询当前redis信息（版本信息、各种配置信息...） [array]    
$redis->watch('key1','key2');                                //监视key1, key2, 必须在事务开启之前监视, 进入事务执行过程 key1, key2 被其他命令所修改，那么事务结果将返回 false [true]   
$redis->unwatch('key1','key2');                              //取消监视key1, key2         
$redis->multi();                                             //开启事务，事务块内的多条命令会按照先后顺序被放进一个队列当中，最后由 exec 命令在一个原子时间内执行。 [object]       
$redis->exec();                                              //执行、提交所有事务块内的命令, [事务块内所有命令执行成功，返回数组，包含每条命令执行结果; 有一条失败返回 false }       
```
