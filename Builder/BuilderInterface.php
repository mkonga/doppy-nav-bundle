<?php

namespace Doppy\NavBundle\Builder;

use Doppy\NavBundle\Nav\Nav;

interface BuilderInterface
{
    /**
     * Returns a NavInterface object with NavItems
     *
     * @param array $options
     *
     * @return Nav
     */
    public function getNav($options = array());
}
