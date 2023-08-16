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
namespace Hyperf\Translation;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\TranslatorLoaderInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        // When registering the translator component, we'll need to set the default
        // locale as well as the fallback locale. So, we'll grab the application
        // configuration so we can easily get both of these values from there.

        $config = $container->get(ConfigInterface::class);
        $locale = $config->get('translation.locale', 'zh_CN');
        $fallbackLocale = $config->get('translation.fallback_locale', 'en');

        $loader = $container->get(TranslatorLoaderInterface::class);

        $translator = make(Translator::class, compact('loader', 'locale'));
        $translator->setFallback((string) $fallbackLocale);

        return $translator;
    }
}
