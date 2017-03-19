<?php

namespace Doppy\NavBundle\DataCollector;

use Doppy\NavBundle\Provider\BuilderProvider;
use Doppy\NavBundle\Provider\NavProvider;
use Doppy\NavBundle\Twig\NavExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class NavDataCollector extends DataCollector implements DataCollectorInterface
{
    /**
     * @var NavProvider
     */
    protected $navProvider;

    /**
     * @var BuilderProvider
     */
    protected $builderProvider;

    /**
     * @var NavExtension
     */
    protected $twigExtension;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * NavDataCollector constructor.
     *
     * @param NavProvider     $navProvider
     * @param BuilderProvider $builderProvider
     * @param NavExtension    $twigExtension
     */
    public function __construct(NavProvider $navProvider, BuilderProvider $builderProvider, NavExtension $twigExtension)
    {
        $this->navProvider     = $navProvider;
        $this->builderProvider = $builderProvider;
        $this->twigExtension   = $twigExtension;
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
        $this->data['providers'] = $this->navProvider->getProviderData();
        $this->data['builders']  = $this->builderProvider->getAvailableBuilders();
        $this->data['rendered']  = $this->twigExtension->getProfilerData();
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
