# KnpOAuthBundle, an OAuth firewall for Symfony2

## Instructions for 2.1 branch

Please note this is the 2.1 compatible branch of this bundle. If you are using Symfony 2.0.\*, then you should upgrade. If you can't, you can still use the [2.0 branch](https://github.com/KnpLabs/KnpOAuthBundle/tree/2.0).

## Introduction

This bundle provides an OAuth firewall for Symfony2.

You should really read the [full documentation](https://github.com/KnpLabs/KnpOAuthBundle/blob/master/Resources/doc/01_index.md), but if you're in a hurry (I know you are), this file should help you quickly getting a working setup.

## Requirements

* Symfony (_2.1 (master branch) or later_)
* Dependencies:
 * [`Buzz`](https://github.com/kriswallsmith/Buzz) (_0.5 or later_)
 * [`SensioBuzzBundle`](https://github.com/sensio/SensioBuzzBundle)

## Installation

Add this to your `deps`:

    [Buzz]
        git=https://github.com/kriswallsmith/Buzz.git
        version=v0.5

    [BuzzBundle]
        git=https://github.com/sensio/SensioBuzzBundle.git
        target=/bundles/Sensio/Bundle/BuzzBundle

    [KnpOAuthBundle]
        git=https://github.com/KnpLabs/KnpOAuthBundle.git
        target=/bundles/Knp/Bundle/OAuthBundle

Then run the usual `bin/vendors`:

    bin/vendors install

Register autoloads:

    $loader->registerNamespaces(array(
        'Knp'              => __DIR__.'/../vendor/bundles',
        'Buzz'             => __DIR__.'/../vendor/Buzz/lib'
    ));

Register the bundles in your `AppKernel`:

    $bundles = array(
        new Knp\Bundle\OAuthBundle\KnpOAuthBundle(),
        new Sensio\Bundle\BuzzBundle\SensioBuzzBundle(),
    );

## Configuration

Using the `KnpOAuthBundle` is just a matter of configuring an `oauth` firewall in your `security.yml`. The bundle exposes a number of configuration directives to suit your oauth needs. Here's a pretty standard security configuration:

    security:
        firewalls:
            login:
                pattern:    ^/secured/login$
                security:   false
            secured_area:
                pattern:    ^/secured/
                oauth:
                    oauth_provider:    oauth
                    authorization_url: https://github.com/login/oauth/authorize
                    access_token_url:  https://github.com/login/oauth/access_token
                    infos_url:         https://github.com/api/v2/json/user/show
                    username_path:     user.login
                    client_id:         <your_oauth_client_id>
                    secret:            <your_oauth_secret>
                    scope:             <your_oauth_scope>
                    check_path:        /secured/login_check
                    login_path:        /secured/login

Please see [the configuration reference](https://github.com/KnpLabs/KnpOAuthBundle/blob/master/Resources/doc/03_configuration.md) for a description of the configuration options.

Right now, what you probably want to know is that this bundle comes with a few pre-configured oauth provider, namely:

* `github` (required options: `client_id`, `secret`)
* er... that's all for now.

If you don't see your favorite provider in the list, don't worry, there are three solutions, depending on how much of a hurry you're in:

1. [Implement it](https://github.com/KnpLabs/KnpOAuthBundle/blob/master/Resources/doc/05_custom_oauth_providers.md) (and it would be awesome if you contributed it afterwards)
2. [Use the generic OAuth provider](https://github.com/KnpLabs/KnpOAuthBundle/blob/master/Resources/doc/04_builtin_oauth_providers.md)
3. [Ask us to implement it](https://github.com/KnpLabs/KnpOAuthBundle/issues/new). (please provide as much information as possible (`authorize_url`, `access_token_url`, `infos_url` (with its response format) and `username_path` would be nice))

## User providers

Most of the time, if you are using Doctrine, you will want to use the `EntityUserProvider`.

This provider fetches users from the database and creates them on the fly if they don't already exist. It requires Doctrine to work. It works exactly like Doctrine's entity user provider, except its configuration key is `oauth_entity`:

    providers:
        secured_area:
            oauth_entity:
                class: KnpBundlesBundle:User
                property: name
