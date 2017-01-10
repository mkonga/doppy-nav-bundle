<?php

namespace Doppy\NavBundle\DependencyInjection\CompilerPass;

use Doppy\UtilBundle\Helper\CompilerPass\BaseTagServiceCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuilderProviderCompilerPass extends BaseTagServiceCompilerPass implements CompilerPassInterface
{
    protected function handleTag(
        ContainerBuilder $containerBuilder,
        Definition $serviceDefinition,
        Reference $taggedServiceReference,
        $attributes
    )
    {
        $serviceDefinition->addMethodCall('addBuilder', array($attributes['provides'], $taggedServiceReference));
    }

    protected function getService(ContainerBuilder $containerBuilder)
    {
        return $containerBuilder->findDefinition('doppy_nav.provider.builder');
    }

    protected function getTaggedServices(ContainerBuilder $containerBuilder)
    {
        return $containerBuilder->findTaggedServiceIds('doppy_nav.builder');
    }

    protected function configureOptionsResolver(OptionsResolver $optionsResolver)
    {
        parent::configureOptionsResolver($optionsResolver);

        $optionsResolver->setRequired('provides');
        $optionsResolver->addAllowedTypes('provides', 'string');
    }

    protected function adjustTaggedService(Definition $definition)
    {
        $definition->setLazy(true);
    }
}
