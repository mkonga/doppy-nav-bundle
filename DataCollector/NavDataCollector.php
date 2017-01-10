<?php

namespace Doppy\NavBundle\DataCollector;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class NavDataCollector extends DataCollector implements DataCollectorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $serviceContainer;

    /**
     * @var Feature
     */
    protected $data = [];

    /**
     * MenuDataCollector constructor.
     *
     * ServiceContainer is used as a dependency to solve a CyclicReference issue.
     * As this is only used on dev, it is not too big of an issue.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * Collect data from the service by the given request.
     *
     * @param Request    $request   The request used.
     * @param Response   $response  The response used.
     * @param \Exception $exception What exception should be used.
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        // get info about providers
        $navProvider             = $this->serviceContainer->get('doppy_nav.provider');
        $this->data['providers'] = $navProvider->getProviderData();

        // get info about builders
        $builderProvider        = $this->serviceContainer->get('doppy_nav.provider.builder');
        $this->data['builders'] = $builderProvider->getAvailableBuilders();

        // twig extension
        if ($this->serviceContainer->has('doppy_nav.twig')) {
            $twigExtension          = $this->serviceContainer->get('doppy_nav.twig');
            $this->data['rendered'] = $twigExtension->getProfilerData();
        }
    }

    /**
     * Returns the collected data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the name of the data collector.
     *
     * @return string
     */
    public function getName()
    {
        return 'doppy_nav';
    }
}
