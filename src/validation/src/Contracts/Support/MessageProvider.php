<?php
/**
 * MessageProvider.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-07-25 18:32
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Validation\Contracts\Support;


use Hyperf\Validation\Contracts\Support\MessageBag;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return MessageBag
     */
    public function getMessageBag();
}
