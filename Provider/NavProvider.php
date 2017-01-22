<?php

namespace Doppy\NavBundle\Provider;

use Doppy\NavBundle\Exception\NavNotFoundException;
use Doppy\NavBundle\Nav\Nav;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class NavProvider implements CacheableProviderInterface
{
    /**
     * @var ProviderInterface|CacheableProviderInterface[]
     */
    protected $providers = array();

    /**
     * @var array
     */
    protected $profilerData = array('calls' => []);

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var AbstractAdapter
     */
    protected $cache;

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * ChainProvider constructor.
     *
     * @param RequestStack $requestStack
     * @param Stopwatch    $stopwatch
     */
    public function __construct(RequestStack $requestStack, $stopwatch = null)
    {
        $this->requestStack = $requestStack;
        $this->stopwatch    = $stopwatch;
    }

    /**
     * @param AbstractAdapter $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param ProviderInterface $provider
     * @param string            $service_id
     */
    public function addProvider(ProviderInterface $provider, $service_id)
    {
        $this->providers[$service_id] = $provider;
    }

    /**
     * @param string $name
     *
     * @return ProviderInterface|CacheableProviderInterface|null
     */
    protected function getProviderFor($name)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($name)) {
                return $provider;
            }
        }
        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        if ($this->getProviderFor($name)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return Nav
     * @throws NavNotFoundException
     */
    public function get($name, $options = array())
    {
        $stopwatchName = 'Doppy\NavBundle\Provider:get(' . $name . ')';
        $this->stopwatch->start($stopwatchName);

        // ensure locale is set as an option
        $options = $this->adjustOptions($options);

        // get provider
        $provider = $this->getProviderFor($name);
        if (!$provider) {
            throw new NavNotFoundException(
                sprintf('None of the providers were able to provide the requested nav %s', $name)
            );
        }

        // maybe check cache
        $cacheItem = false;
        if (($this->cache) && ($provider instanceof CacheableProviderInterface) && ($provider->isCacheable($name))) {
            $cacheItem = $this->cache->getItem($this->createCacheKey($provider, $name, $options));
            if ($cacheItem->isHit()) {
                $duration = $this->stopwatch->stop($stopwatchName);
                $this->addProfilerData('from cache', $name, $duration, $cacheItem);
                return $cacheItem->get();
            }
        }

        // retrieve Nav
        $nav = $provider->get($name, $options);

        // Save to cache when CacheItem is valid
        if ($cacheItem) {
            $cacheItem->set($nav);
            $cacheItem->tag($provider->getCacheTags($name, $options));
            $this->cache->save($cacheItem);
        }

        // return Nav
        $duration = $this->stopwatch->stop($stopwatchName);
        $this->addProfilerData('provided', $name, $duration, $cacheItem);
        return $nav;
    }

    public function isCacheable($name, $options = array())
    {
        $options = $this->adjustOptions($options);

        $provider = $this->getProviderFor($name);
        if ($provider instanceof CacheableProviderInterface) {
            return $provider->isCacheable($name, $options);
        }
        return false;
    }


    public function getCacheKeySuffix($name, $options = array())
    {
        $options = $this->adjustOptions($options);

        $provider = $this->getProviderFor($name);
        if ($provider instanceof CacheableProviderInterface) {
            return $provider->getCacheKeySuffix($name, $options);
        }
        return '';
    }

    public function getCacheTags($name, $options = array())
    {
        $options = $this->adjustOptions($options);

        $provider = $this->getProviderFor($name);
        if ($provider instanceof CacheableProviderInterface) {
            return $provider->getCacheTags($name, $options);
        }
        return [];
    }

    /**
     * Ensures the options has a _locale set
     *
     * @param array $options
     *
     * @return array
     */
    public function adjustOptions($options)
    {
        if (!isset($options['_locale'])) {
            $options['_locale'] = $this->requestStack->getCurrentRequest()->get('_locale');
            return $options;
        }
        return $options;
    }

    /**
     * Returns some information about the configured providers for the profiler
     *
     * @return array
     */
    public function getProviderData()
    {
        $data = $this->profilerData;
        foreach ($this->providers as $serviceId => $service) {
            $row['service_id']   = $serviceId;
            $row['cacheable']    = ($service instanceof CacheableProviderInterface);
            $data['providers'][] = $row;
        }
        return $data;
    }

    /**
     * @param string         $result
     * @param string         $name
     * @param StopwatchEvent $duration
     * @param CacheItem      $cacheItem
     */
    private function addProfilerData($result, $name, $duration = null, $cacheItem = null)
    {
        $data['name']     = $name;
        $data['result']   = $result;
        $data['duration'] =  '';
        if ($duration) {
            $data['duration'] = $duration->getDuration();
        }
        $data['cachekey'] = '';
        if ($cacheItem) {
            $data['cachekey'] = $cacheItem->getKey();
        }
        $this->profilerData['calls'][] = $data;
    }

    /**
     * @param CacheableProviderInterface $provider
     * @param string                     $name
     * @param array                      $options
     *
     * @return string
     */
    private function createCacheKey($provider, $name, $options)
    {
        return implode(
            '-',
            array(
                'doppy_nav', 'provide',
                $name, md5(serialize($options)),
                $provider->getCacheKeySuffix($name, $options)
            )
        );
    }
}
