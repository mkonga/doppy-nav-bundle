<?php

namespace Doppy\NavBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CacheCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $providerCacheId = $container->getParameter('doppy_nav.cache.provider');
        if ($providerCacheId) {
            $providerDefinition = $container->getDefinition('doppy_nav.provider');
            $providerDefinition->addMethodCall('setCache', [new Reference($providerCacheId)]);
        }
        
        $renderCacheId = $container->getParameter('doppy_nav.cache.render');
        if (($renderCacheId) && ($container->has('doppy_nav.twig'))) {
            $navExtensionDefintion = $container->getDefinition('doppy_nav.twig');
            $navExtensionDefintion->addMethodCall('setCache', [new Reference($renderCacheId)]);
        }
    }
}
