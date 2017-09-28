Step 1: Setting up the bundle
=============================
### A) Add HWIOAuthBundle to your project

```bash
composer require hwi/oauth-bundle
```

This bundle uses the [HTTPlug library](http://docs.php-http.org/en/latest/#httplug).

If it is not already done, you must also require on of the
[HTTPlug client's adapters](http://docs.php-http.org/en/latest/clients.html#clients-adapters).
For example:

```bash
composer require php-http/guzzle6-adapter
```

### B) Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
    );
}
```

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
