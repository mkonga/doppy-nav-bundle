<?php

namespace Doppy\NavBundle\Provider;

use Doppy\NavBundle\Builder\AdvancedCacheableBuilderInterface;
use Doppy\NavBundle\Builder\BuilderInterface;
use Doppy\NavBundle\Builder\CacheableBuilderInterface;
use Doppy\NavBundle\Exception\NavNotFoundException;

class BuilderProvider implements CacheableProviderInterface
{
    /**
     * @var BuilderInterface[]
     */
    protected $builders = array();

    /**
     * @var array
     */
    protected $requested = array();

    /**
     * @param string           $name
     * @param BuilderInterface $builder
     */
    public function addBuilder($name, BuilderInterface $builder)
    {
        $this->builders[$name] = $builder;
    }

    public function has($name)
    {
        return (isset($this->builders[$name]));
    }

    public function get($name, $options = array())
    {
        if (!isset($this->requested[$name])) {
            $this->requested[$name] = 0;
        }
        $this->requested[$name]++;
        return $this->getBuilder($name)->getNav($options);
    }

    public function isCacheable($name, $options = array())
    {
        return ($this->builders[$name] instanceof CacheableBuilderInterface);
    }

    public function getCacheKeySuffix($name, $options = array())
    {
        $builder = $this->getBuilder($name);
        if ($builder instanceof AdvancedCacheableBuilderInterface) {
            return $builder->getCacheKeySuffix($options);
        }
        return null;
    }

    public function getCacheTags($name, $options = array())
    {
        $builder = $this->getBuilder($name);
        if ($builder instanceof AdvancedCacheableBuilderInterface) {
            return $builder->getCacheTags($options);
        }
        return [];
    }

    public function getBuilder($name)
    {
        // check existence
        if (!$this->has($name)) {
            throw new NavNotFoundException();
        }

        return $this->builders[$name];
    }

    /**
     * Returns soms data for the profiler
     *
     * @return array
     */
    public function getAvailableBuilders()
    {
        $data = array();
        foreach ($this->builders as $name => $builder) {
            $row['name']      = $name;
            $row['requested'] = isset($this->requested[$name]) ? $this->requested[$name] : 0;
            $row['cacheable'] = 'no';
            if ($builder instanceof AdvancedCacheableBuilderInterface) {
                $row['cacheable'] = 'advanced';
            } elseif ($builder instanceof CacheableBuilderInterface) {
                $row['cacheable'] = 'simple';
            }
            $data[]           = $row;
        }

        return $data;
    }
}
