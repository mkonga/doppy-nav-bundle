<?php

namespace Doppy\NavBundle\DependencyInjection\CompilerPass;

use Doppy\NavBundle\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class BuilderProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $containerBuilder)
    {
        $providerBuilderDefinition = $containerBuilder->findDefinition('doppy_nav.provider.builder');
        $taggedBuilders            = $this->getTaggedBuilders($containerBuilder);

        foreach ($taggedBuilders as $priority => $taggedBuilder) {
            // check attribute
            if (!isset($taggedBuilder['attributes']['provides'])) {
                throw $this->createInvalidArgumentException($taggedBuilder['service_id']);
            }

            $providerBuilderDefinition->addMethodCall(
                'addBuilder',
                array($taggedBuilder['attributes']['provides'], new Reference($taggedBuilder['service_id']))
            );
        }
    }

    /**
     * @param ContainerBuilder $containerBuilder
     *
     * @return array
     */
    protected function getTaggedBuilders(ContainerBuilder $containerBuilder)
    {
        $services = array();
        foreach ($containerBuilder->findTaggedServiceIds('doppy_nav.builder') as $serviceId => $tags) {
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

    /**
     * Creates an exception
     *
     * @param string $serviceId
     *
     * @return InvalidConfigurationException
     */
    private function createInvalidArgumentException($serviceId)
    {
        return new InvalidConfigurationException(
            sprintf('Required attribute "provides" is missing on tag "doppy_nav.builder" for service "%s".', $serviceId)
        );
    }

}
