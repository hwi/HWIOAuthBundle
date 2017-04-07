Step 1: Setting up the bundle
=============================
### A) Add HWIOAuthBundle to your project

```bash
composer require hwi/oauth-bundle
```

**OR (if you're using deps file)**

Add the following to the deps file:

```
[buzz]
    git=http://github.com/kriswallsmith/Buzz.git
[SensioBuzzBundle]
    git=http://github.com/sensio/SensioBuzzBundle.git
    target=bundles/Sensio/Bundle/BuzzBundle
[HWIOAuthBundle]
    git=git://github.com/hwi/HWIOAuthBundle.git
    target=bundles/HWI/Bundle/OAuthBundle
```


Now use the vendors script to clone the newly added repositories into your project:
```
php bin/vendors install
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

Make sure that you also register the namespaces with the autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'HWI'           => __DIR__.'/../vendor/bundles',
));
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
