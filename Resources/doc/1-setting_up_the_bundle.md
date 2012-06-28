Step 1: Setting up the bundle
=============================
### A) Add HWIOAuthBundle to your composer.json

``` js
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
        new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
    );
}
```

**Note:**

> This bundle has a dependency on the `SensioBuzzBundle` so ensure that you 
> have registered it in the kernel as above.

### Continue to the next step!
When you're done. Continue by configuring the resource owners you want to use
in your application!
[Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)