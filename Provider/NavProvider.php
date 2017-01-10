<?php

namespace Doppy\NavBundle\Provider;

use Doppy\NavBundle\Exception\NavNotFoundException;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Psr\Cache\CacheItemPoolInterface;

class NavProvider implements CacheableProviderInterface
{
    /**
     * @var ProviderInterface[]
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

    public function has($name)
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($name)) {
                return true;
            }
        }
        return false;
    }

    public function get($name, $options = array())
    {
        $stopwatchName = 'Doppy\NavBundle\Provider:get(' . $name . ')';
        $this->stopwatch->start($stopwatchName);

        // ensure locale is set as an option
        $options = $this->adjustOptions($options);

        // maybe check cache
        $cacheItem = $this->getCacheItem($name, $options);
        if (($cacheItem) && ($cacheItem->isHit())) {
            $this->addProfilerData('fromcache', $name);
            $this->stopwatch->stop($stopwatchName);
            return $cacheItem->get();
        }

        foreach ($this->providers as $provider) {
            try {
                // retrieve Nav
                $nav      = $provider->get($name, $options);

                // add some profiling
                $duration = $this->stopwatch->stop($stopwatchName);
                $this->addProfilerData('success', $name, $duration);

                // Save to cache when CacheItem is valid
                if ($cacheItem) {
                    $cacheItem->set($nav);
                    $this->cache->save($cacheItem);
                }

                // return Nav
                return $nav;
            } catch (NavNotFoundException $e) {
                // no action;
            }
        }

        $duration = $this->stopwatch->stop($stopwatchName);
        $this->addProfilerData('not found', $name, $duration);
        throw new NavNotFoundException(
            sprintf('None of the providers were able to provide the requested nav %s', $name)
        );
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @return CacheItemPoolInterface|null
     */
    protected function getCacheItem($name, $options = array())
    {
        // check if cache is available
        if (!$this->cache) {
            return null;
        }

        // determine cache key to use
        $cacheKey = implode(
            '-',
            array(
                'doppy_nav', 'nav',
                $name, md5(serialize($options)),
                $this->getCacheKeySuffix($name, $options)
            )
        );
        return $this->cache->getItem($cacheKey);
    }

    public function getCacheKeySuffix($name, $options = array())
    {
        $options = $this->adjustOptions($options);

        foreach ($this->providers as $provider) {
            try {
                if ($provider instanceof CacheableProviderInterface) {
                    $cacheKey = $provider->getCacheKeySuffix($name, $options);
                    if (!empty($cacheKey)) {
                        return $cacheKey . '.nav.' . md5(serialize($options));
                    }
                }
            } catch (NavNotFoundException $e) {
                // no action;
            }
        }

        // still here? guess no Provider can generate it or there is no cache key for it
        return null;
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
     */
    private function addProfilerData($result, $name, $duration = null)
    {
        $data['name']   = $name;
        $data['result'] = $result;
        if ($duration instanceof StopwatchEvent) {
            $data['duration'] = $duration->getDuration();
        } else {
            $data['duration'] = null;
        }
        $this->profilerData['calls'][] = $data;
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
}
