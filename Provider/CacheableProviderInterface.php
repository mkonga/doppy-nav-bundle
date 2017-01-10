<?php

namespace Doppy\NavBundle\Provider;

use Doppy\NavBundle\Exception\NavNotFoundException;

Interface CacheableProviderInterface extends ProviderInterface
{
    /**
     * This method may return a part of string that should be part of the cache key (to make it unique)
     *
     * The name of the Nav and it's options are already used in the cache key. The parameter `_locale` is included it the options.
     *
     * The method does not need to check if the Nav actually exists.
     * It *may* throw an NavNotFoundException when it knows it does not exist, but it does not have to.
     *
     * It is recommended to not fetch information from a database (or other slow storage) if not really needed.
     * Only retrieve information that is needed for the cache key.
     * If the Nav doesn't actually exist, there won't be a cache hit, resulting in it being generated (and failing at that point).
     *
     * @param string $name
     * @param array  $options
     *
     * @return string|null
     *
     * @throws NavNotFoundException
     */
    public function getCacheKeySuffix($name, $options = array());
}
