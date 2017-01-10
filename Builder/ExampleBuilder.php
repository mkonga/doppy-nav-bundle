<?php

namespace Doppy\NavBundle\Builder;

use Doppy\NavBundle\Nav\Nav;
use Doppy\NavBundle\Nav\NavItem;
use Symfony\Component\Routing\RouterInterface;

class ExampleBuilder implements CacheableBuilderInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getNav($options = array())
    {
        // main object
        $nav = new Nav();

        // add some items
        $nav->addChild(new NavItem($this->router->generate('login', 'Log in')));

        // return the object
        return $nav;
    }

    public function getCacheKeySuffix($options = array())
    {
        return null;
    }
}
