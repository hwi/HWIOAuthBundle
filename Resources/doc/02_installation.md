# KnpOAuthBundle

## Installation

Installing the `KnpOAuthBundle` looks a lot like installing any other bundle.

This bundle requires [`SensioBuzzBundle`](https://github.com/sensio/SensioBuzzBundle) to work, which in turn requires the [`Buzz`](https://github.com/kriswallsmith/Buzz) library. So add the following lines to your `deps` file:

    [Buzz]
        git=https://github.com/kriswallsmith/Buzz.git
        version=v0.5

    [BuzzBundle]
        git=https://github.com/sensio/SensioBuzzBundle.git
        target=/bundles/Sensio/Bundle/BuzzBundle

    [KnpOAuthBundle]
        git=https://github.com/KnpLabs/KnpOAuthBundle.git
        target=/bundles/Knp/Bundle/OAuthBundle

Of course, if you already use `Buzz` and/or the `SensioBuzzBundle`, don't add them again.

Then you need to update your vendors:

  bin/vendors install

If you don't already have bundles in the `Knp` or `Sensio` namespaces, you will need to register these namespaces to your `app/autoload.php`:

    $loader->registerNamespaces(array(
        'Knp\\Bundle'      => __DIR__.'/../vendor/bundles',
        'Buzz'             => __DIR__.'/../vendor/Buzz/lib'
    ));

You're now ready to register the bundles into your `app/AppKernel.php`:

    $bundles = array(
        new Knp\Bundle\OAuthBundle\KnpOAuthBundle(),
        new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
    );


Great! Everything is ready, let's proceed to the [configuration](03_configuration.md)!