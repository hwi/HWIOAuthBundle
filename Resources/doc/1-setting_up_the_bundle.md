Step 1: Setting up the bundle
=============================
### A) Add HWIOAuthBundle to your composer.json

``` yaml
{
    "require": {
        "hwi/oauth-bundle": "*"
    }
}
```

### B) Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
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

Import the `redirect.xml` routing file in your own routing file.

``` yaml
# app/config/routing.yml
hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /connect
```

**Note:**

> To prevent strange issues, this route should be imported before your custom ones.

### D) Working with Symfony 2.0

If you use Symfony 2.0, you need to import the `security_factory.xml` in your `security.yml`:

``` yaml
# app/config/security.yml
security:
    factories:
        - "%kernel.root_dir%/../vendor/bundles/HWI/Bundle/OAuthBundle/Resources/config/security_factory.xml"
```

**Note:**

> This step is done automatically if you use Symfony 2.1

### Continue to the next step!
When you're done. Continue by configuring the resource owners you want to use
in your application!
[Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
