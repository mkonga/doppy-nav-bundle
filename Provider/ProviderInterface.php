<?php

namespace Doppy\NavBundle\Provider;

use Doppy\NavBundle\Exception\NavNotFoundException;
use Doppy\NavBundle\Nav\Nav;

Interface ProviderInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name);

    /**
     * @param string $name
     * @param array  $options
     *
     * @return Nav
     *
     * @throws NavNotFoundException
     */
    public function get($name, $options = array());
}
