<?php

namespace Doppy\NavBundle\DependencyInjection\CompilerPass;

use Doppy\UtilBundle\Helper\CompilerPass\BaseTagServiceCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChainProviderCompilerPass extends BaseTagServiceCompilerPass implements CompilerPassInterface
{
    protected function handleTag(
        ContainerBuilder $containerBuilder,
        Definition $serviceDefinition,
        Reference $taggedServiceReference,
        $attributes
    )
    {
        $serviceDefinition->addMethodCall('addProvider', array($taggedServiceReference, $attributes['service_id']));
    }

    protected function getService(ContainerBuilder $containerBuilder)
    {
        return $containerBuilder->findDefinition('doppy_nav.provider');
    }

    protected function getTaggedServices(ContainerBuilder $containerBuilder)
    {
        return $containerBuilder->findTaggedServiceIds('doppy_nav.provider');
    }

    protected function configureOptionsResolver(OptionsResolver $optionsResolver)
    {
        parent::configureOptionsResolver($optionsResolver);
    }

    protected function adjustTaggedService(Definition $definition)
    {
        $definition->setLazy(true);
    }
}
