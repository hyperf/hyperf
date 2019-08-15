<?php
/**
 * TranslatorFactory.php
 *
 * Author: wangyi <chunhei2008@qq.com>
 *
 * Date:   2019-07-25 16:41
 * Copyright: (C) 2014, Guangzhou YIDEJIA Network Technology Co., Ltd.
 */

namespace Hyperf\Translation;


use Hyperf\Translation\Contracts\Loader;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // When registering the translator component, we'll need to set the default
        // locale as well as the fallback locale. So, we'll grab the application
        // configuration so we can easily get both of these values from there.

        $config         = $container->get(ConfigInterface::class);
        $locale         = $config->get('translation.locale');
        $fallbackLocale = $config->get('translation.fallback_locale');

        $loader = $container->get(Loader::class);

        $trans = make(Translator::class, compact('loader', 'locale'));
        $trans->setFallback($fallbackLocale);


        return $trans;

    }
}