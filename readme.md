# Doppy Nav Bundle

A Symfony3 bundle providing some useful tools to create menu's and make them cacheable.

## installation

### add to composer

````
    "require": {
        "doppy/nav-bundle": "^1.0.0",
    }
````

### add to AppKernel

````
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Doppy\UtilBundle\DoppyUtilBundle(),
            new Doppy\NavBundle\DoppyNavBundle(),
            // ...
        );
    }
````

The DoppyUtilBundle also needs to be loaded for some small dependencies and simplification of service definitions.

## Create your own NavBuilder

You can create your own NavBuilder service. This service will return a `Nav` object containing `NavItem`'s, which is effectively your navigation or menu.

Configure your service in symfony, adding a simple tag to make it available:

````
services:
    my_bundle.nav.sidebar.builder:
        class: \My\MyBundle\NavBuilder\SideBarBuilder
        tags:
            - { name: "doppy_nav.builder", provides: "sidebar" }
````

The parameter `provides` is mandatory and is the name under which your Nav will be available.

You can add your own depenencies when needed. Useful suggestions could be `tokenstorage` and `authorization_checker`, if you want to customize your Nav for a specific user or based on permissions.

You can decide yourself what you want to do with translations of nav-labels. You can set the correct text in the Builder itself, or run the label through the translator when rendering the template.

Both the `Nav` and `NavItem` objects have a property `attributes` available, which you can use freely to configure additional properties you might need when rendering.

## Use your Nav

There are several ways to use the Nav you created:

### Retrieve it from the Provider

You can just request the Nav object from the provider. This way you could insert one Nav into another.

````
$navProvider = $this->getServiceContainer()->get('doppy_nav.provider');
$nav = $navProvider->get('sidebar', array('youroption' => 'yourvalue'));
````

* The second argument can be used to pass options to your builder and is optional.
* If the options does not contain the key `_locale`, it will be added by using the current locale.

### Retrieve it in Twig

By retrieving the object in Twig, you can then use this to render it in any way you like.

````
{{ set nav = doppy_nav('sidebar', ['youroption': 'yourvalue']) }}
````

This is the same as the `get` method on the Provider (see above).

### Render it in Twig

You can also directly render the Nav in twig. This is a more clean solution, and also supports caching. 

````
{{ doppy_nav_render('sidebar', [], 'YourBundle:Nav:nav.html.twig', []) }}
````

* The first 2 arguments are passed to the Provider for fetching the Nav object.
* The third argument is the template to be used for rendering. If not provided, a default template is used.
* The fourth argument are passed to the template for rendering. The key `nav` is reserved, as the Nav object will be passed in there.

## Cache your Nav's

Because chances are it doesn't change that often, it might be a good idea to cache your Nav.

### Configure Caching

Make sure the `symfony/cache` component is installed (via composer), as this is used for caching. Adjust your config to enable caching:

````yaml
doppy_nav:
    cache:
        provider: "cache.app"
        render:   "cache.app"
````

You need to specify the name of the cache service to use.

* `provider` will cache the result of the Provider (in effect the Nav object)
* `render` will cache the result of the twig render function.

Which cache you use is up to you, but you might not need both. In most simple cases you would only need to enable `render`.

Cache is disabled by default.

To adjust the cache duration, adjust the configuration of the supplied service.

### Cacheable Builder

To make your Builder cacheable, simply implement the interface `Doppy\NavBundle\Builder\CacheableBuilderInterface`.
You now need to provide an additional method that supplies the cache-key to use when the Nav is requested. Usually the basic name will suffice, but if you customize your Nav for a specific user, you would want to add the username or userid.

A suffix will be appended to your cachekey, this is constructed from:

* Builder:
* * An md5 hash of the options provided.
* Render:
* * Whatever hash the provider supplies (this will also include )
* * An md5 hash of the template and template options provided.

### Invalidation

At the moment there is no cache invalidation configured. You just need to wait until it expires, or clear all symfony caches.


## Advanced: Create your own provider

A builder can only create 1 nav (but you can tweak this a bit with options). You can add your own Provider that is capable of managing a large set of Nav's. You need to implement the interface `\Doppy\NavBundle\Provider\ProviderInterface`.
This can be useful if wou want to manage them in a database for instance.

To see how to do this, take a look at the service `doppy_nav.provider.builder`.

You can also use `\Doppy\NavBundle\Provider\CacheableProviderInterface` to make your provider cacheable. 
