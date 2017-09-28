Step 1: Setting up the bundle
=============================
### A) Add HWIOAuthBundle to your project

```bash
composer require hwi/oauth-bundle php-http/guzzle6-adapter php-http/httplug-bundle
```

Why `php-http/guzzle6-adapter`? We are decoupled from any HTTP messaging client with help by [HTTPlug](http://httplug.io/).

Why `php-http/httplug-bundle`? This is the official [Symfony integration](https://packagist.org/packages/php-http/httplug-bundle) of HTTPlug.
This permit to provide the required HTTP client and message factory with ease.
The dependency is optional but you will have to [provide your own services](internals/configuring_the_http_client.md) if you don't setup it.

### B) Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Http\HttplugBundle\HttplugBundle(), // If you require the php-http/httplug-bundle package.
        new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
    );
}
```

Configure at least one httplug client from `HttplugBundle`. The default one will be use if not specified.

See the [vendor documentation](http://docs.php-http.org/en/latest/integrations/symfony-bundle.html#installation) for mor information.

### C) Import the routing

Import the `redirect.xml` and `login.xml` routing files in your own routing file.

```yaml
# app/config/routing.yml
hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /connect
    
hwi_oauth_connect:
    resource: "@HWIOAuthBundle/Resources/config/routing/connect.xml"
    prefix:   /connect

hwi_oauth_login:
    resource: "@HWIOAuthBundle/Resources/config/routing/login.xml"
    prefix:   /login
```

**Note:**

> To prevent strange issues, this route should be imported before your custom ones.

### Continue to the next step!
When you're done. Continue by configuring the resource owners you want to use
in your application!


[Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
