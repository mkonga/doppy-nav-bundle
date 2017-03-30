<?php

namespace Doppy\NavBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ChainProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $providerDefinition = $containerBuilder->findDefinition('doppy_nav.provider');
        $taggedProviders    = $this->getTaggedProviders($containerBuilder);

        foreach ($taggedProviders as $priority => $taggedProvider) {
            // make provider lazy
            $taggedProviderDefinition = $containerBuilder->getDefinition($taggedProvider['service_id']);
            $taggedProviderDefinition->setLazy(true);

            // add to provider
            $providerDefinition->addMethodCall(
                'addProvider',
                array(new Reference($taggedProvider['service_id']), $taggedProvider['service_id'])
            );
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     *
     * @return array
     */
    protected function getTaggedProviders(ContainerBuilder $containerBuilder)
    {
        $services = array();
        foreach ($containerBuilder->findTaggedServiceIds('doppy_nav.provider') as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                // determine priority
                $attributes['priority']              = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $services[$attributes['priority']][] = array('service_id' => $serviceId, 'attributes' => $attributes);
            }
        }
        krsort($services);
        $returnServices = [];
        foreach ($services as $nested) {
            foreach ($nested as $service) {
                $returnServices[] = $service;
            }
        }
        return $returnServices;
    }
}
