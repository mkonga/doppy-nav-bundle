<?php

namespace Doppy\NavBundle\Twig;

use Doppy\NavBundle\Provider\ProviderInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class NavExtension extends \Twig_Extension
{
    /**
     * @var ProviderInterface
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
        $this->navProvider  = $navProvider;
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
     * @param \Twig_Environment $twig
     * @param string            $name
     * @param array             $options
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
     * @param array             $options
     * @param string            $template
     * @param array             $templateParameters
     *
     * @return string
     */
    public function render(
        \Twig_Environment $twig,
        $name,
        $options = array(),
        $template = 'DoppyNavBundle:Nav:nav.html.twig',
        $templateParameters = array()
    )
    {
        // start stopwatch
        $stopwatchName = 'DoppyNav\Twig:Render(' . $name . ')';
        $this->stopwatch->start($stopwatchName);

        // ensure locale is set as an option
        $options = $this->navProvider->adjustOptions($options);

        // maybe check cache
        $cacheItem = $this->getCacheItem($name, $options, $template, $templateParameters);
        if (($cacheItem) && ($cacheItem->isHit())) {
            $this->addProfilerData('fromcache', $name);
            $this->stopwatch->stop($stopwatchName);
            return $cacheItem->get();
        }

        // retrieve nav
        $nav = $this->navProvider->get($name, $options);

        // now render
        $templateParameters['nav'] = $nav;
        $rendered                  = $twig->render($template, $templateParameters);

        // Save to cache when CacheItem is valid
        if ($cacheItem) {
            $cacheItem->set($rendered);
            $this->cache->save($cacheItem);
        }

        // return what was rendered
        $duration = $this->stopwatch->stop($stopwatchName);
        $this->addProfilerData('rendered', $name, $duration);
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
        $this->profilerData[] = $data;
    }

    /**
     * @return array
     */
    public function getProfilerData()
    {
        return $this->profilerData;
    }
}
