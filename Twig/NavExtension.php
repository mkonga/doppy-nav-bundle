<?php

namespace Doppy\NavBundle\Twig;

use Doppy\NavBundle\Provider\NavProvider;
use Doppy\NavBundle\Provider\ProviderInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Psr\Cache\CacheItemPoolInterface;

class NavExtension extends \Twig_Extension
{
    /**
     * @var NavProvider
     */
    protected $navProvider;

    /**
     * @var AbstractAdapter|null
     */
    protected $cache;

    /**
     * @var array
     */
    protected $profilerData = array();

    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @param ProviderInterface $navProvider
     * @param Stopwatch         $stopwatch
     */
    public function __construct(ProviderInterface $navProvider, $stopwatch)
    {
        $this->navProvider = $navProvider;
        $this->stopwatch   = $stopwatch;
    }

    /**
     * @param AbstractAdapter $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('doppy_nav', array($this, 'get')),
            new \Twig_SimpleFunction(
                'doppy_nav_render',
                array($this, 'render'),
                array(
                    'needs_environment' => true,
                    'is_safe'           => ['html']
                )
            )
        );
    }

    /**
     * @param string $name
     * @param array  $options
     *
     * @throws \Exception
     * @return string
     */
    public function get($name, $options = array())
    {
        return $this->navProvider->get($name, $options);
    }

    /**
     * @param \Twig_Environment $twig
     * @param string            $name
     * @param string            $template
     * @param array             $navOptions
     * @param array             $templateParameters
     *
     * @return string
     */
    public function render(
        \Twig_Environment $twig,
        $name,
        $template = 'DoppyNavBundle:Nav:nav.html.twig',
        $navOptions = array(),
        $templateParameters = array()
    )
    {
        // start stopwatch
        $stopwatchName = 'DoppyNav\Twig:Render(' . $name . ')';
        $this->stopwatch->start($stopwatchName);

        // ensure locale is set as an option
        $navOptions = $this->navProvider->adjustOptions($navOptions);

        // maybe check cache
        $cacheItem = false;
        if (($this->cache) && ($this->navProvider->isCacheable($name, $navOptions))) {
            $cacheItem = $this->cache->getItem($this->createCacheKey($name, $navOptions, $template, $templateParameters));
            if ($cacheItem->isHit()) {
                $duration = $this->stopwatch->stop($stopwatchName);
                $this->addProfilerData('from cache', $name, $duration, $cacheItem);
                return $cacheItem->get();
            }
        }

        // retrieve nav
        $nav = $this->navProvider->get($name, $navOptions);

        // now render
        $templateParameters['nav'] = $nav;
        $rendered                  = $twig->render($template, $templateParameters);

        // Save to cache when CacheItem is valid
        if ($cacheItem) {
            $cacheItem->set($rendered);
            $cacheItem->tag($this->navProvider->getCacheTags($name, $navOptions));
            $this->cache->save($cacheItem);
        }

        // return what was rendered
        $duration = $this->stopwatch->stop($stopwatchName);
        $this->addProfilerData('rendered', $name, $duration, $cacheItem);
        return $rendered;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param string $template
     * @param array  $templateParameters
     *
     * @return CacheItemPoolInterface|null
     */
    protected function getCacheItem($name, $options, $template, $templateParameters)
    {
        // check if cache is available
        if (!$this->cache) {
            return null;
        }

        // determine cache key to use
        $cacheKey = implode(
            '-',
            array(
                'doppy_nav', 'render',
                $name, md5(serialize($options)),
                md5(serialize([$template, $templateParameters])),
                $this->navProvider->getCacheKeySuffix($name, $options)
            )
        );
        return $this->cache->getItem($cacheKey);
    }

    public function getName()
    {
        return 'doppy_nav';
    }

    /**
     * @param string         $result
     * @param string         $name
     * @param StopwatchEvent $duration
     * @param CacheItem      $cacheItem
     */
    private function addProfilerData($result, $name, $duration = null, $cacheItem)
    {
        $data['name']     = $name;
        $data['result']   = $result;
        $data['duration'] = '';
        if ($duration instanceof StopwatchEvent) {
            $data['duration'] = $duration->getDuration();
        }
        $data['cachekey'] = '';
        if ($cacheItem) {
            $data['cachekey'] = $cacheItem->getKey();
        }
        $this->profilerData[] = $data;
    }

    /**
     * @return array
     */
    public function getProfilerData()
    {
        return $this->profilerData;
    }

    /**
     * @param string $name
     * @param array  $options
     * @param string $template
     * @param array  $templateParameters
     *
     * @return string
     */
    private function createCacheKey($name, $options, $template, $templateParameters)
    {
        return implode(
            '-',
            array(
                'doppy_nav', 'render',
                $name,
                md5(serialize([$options, $template, $templateParameters])),
                $this->navProvider->getCacheKeySuffix($name, $options)
            )
        );
    }
}
