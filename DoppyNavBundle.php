<?php

namespace Doppy\NavBundle;

use Doppy\NavBundle\DependencyInjection\CompilerPass\BuilderProviderCompilerPass;
use Doppy\NavBundle\DependencyInjection\CompilerPass\CacheCompilerPass;
use Doppy\NavBundle\DependencyInjection\CompilerPass\ChainProviderCompilerPass;
use Doppy\RoutingBundle\DependencyInjection\CompilerPass\ChainRouterCompilerPass;
use Doppy\RoutingBundle\DependencyInjection\CompilerPass\ReplaceRouterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoppyNavBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
        $container->addCompilerPass(new ChainProviderCompilerPass());
        $container->addCompilerPass(new BuilderProviderCompilerPass());
    }
}
