# KnpOAuthBundle, an OAuth firewall for all your Symfony2 OAuth needs.

## TODO:

* implement a PropelUserProvider
* code-cleanup
* unit tests

## Requirements

This bundle requires Symfony 2.1 or later to work.

## Installation

This bundle depends on Buzz, the lightweight HTTP client by awesome @kriswallsmith, so add it to your project's deps file alongside the bundle itself. Also, since we're lazy, we'll use sensiolabs' `BuzzBundle` by no less awesome @marcw:

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
                    oauth_provider:   oauth
                    authorize_url:    https://github.com/login/oauth/authorize
                    access_token_url: https://github.com/login/oauth/access_token
                    infos_url:        https://github.com/api/v2/json/user/show
                    username_path:    user.login
                    client_id:        <your_oauth_client_id>
                    secret:           <your_oauth_secret>
                    scope:            <your_oauth_scope>
                    check_path:       /secured/login_check
                    login_path:       /secured/login

Here's a quick description of what each directive means:

* `oauth_provider` is the OAuth provider name to use. This bundle comes with a few builtin providers:
 * `oauth`, a basic provider which needs all of the options to work. This is the default provider if you don't specify `oauth_provider`.
 * the `github` provider encapsulates a few options. Basically, you don't need the options marked *optional* below.
 * if your `oauth_provider` contains a dot (`.`), it will be considered as a fully qualified service name and the bundle will attempt to retrieve it from the DIC.
* `authorize_url` (*optional*) is the OAuth authorization URL provided by your OAuth provider.
* `access_token_url` (*optional*) is the OAuth access token url, courtesy of your OAuth provider.
* `infos_url` (*optional*) is the URL where your provider will give you infos about your user. This option is always optional (but it requires `username_path` to work). If it's not set, the `access_token` will be used as the `username`.
* `username_path` (*optional*) is the dot-separated array path where to find the username in the response content of `infos_url` (e.g. github returns an array in which `$foo['user']['login']` holds the user's login, so we set `username_path` to `user.login`). Like `infos_url`, this option is always optional, but you will need to set `infos_url` to use it.
* `client_id` is the... client id, provided by... your OAuth provider.
* `secret`, do I really have to explain? hint: it's provided by your OAuth provider too.
* `scope` is how much information and authorization you wish to access.

The `check_path` and `login_path` directives are standard Symfony2 security configuration. You should read the [security documentation](http://symfony.com/doc/current/book/security.html) if you're not yet familiar with them.

## Built-in OAuth providers

Built-in providers exist to ease configuration of your OAuth firewall. They come pre-configured and they can have custom mechanisms to retrieve the user's username.

Here's a quick example using the `GithubProvider`:

    security:
        firewalls:
            login:
                pattern:    ^/secured/login$
                security:   false
            secured_area:
                pattern:    ^/secured/
                oauth:
                    oauth_provider:   github
                    client_id:        <your_oauth_client_id>
                    secret:           <your_oauth_secret>
                    scope:            <your_oauth_scope>
                    check_path:       /secured/login_check
                    login_path:       /secured/login

Sounds easy? That's because it is.

Of course, you can still set a custom `username_path` when using a builtin provider. Actually, you can override any pre-configured option.

## Using your own custom providers

You can write your own OAuth providers to use with this bundle. A custom OAuth provider consists in a class implementing `Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface` and the corresponding DIC service.

If your custom provider is simple enough, you can also extend `Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProvider`, see the `GithubProvider` for more information on that.

One your custom provider is writen and added to the DIC, you just have to configure `oauth_provider` to its service name. For example, say you implemented a `My\FooBarProvider` and added a service for it named `my.oauth.foobar_provider`, then your configuration would become:

    security:
        firewalls:
            login:
                pattern:    ^/secured/login$
                security:   false
            secured_area:
                pattern:    ^/secured/
                oauth:
                    oauth_provider:   my.oauth.foobar_provider
                    client_id:        <your_oauth_client_id>
                    secret:           <your_oauth_secret>
                    scope:            <your_oauth_scope>
                    check_path:       /secured/login_check
                    login_path:       /secured/login

## User providers

This bundle comes with a few custom `UserProvider`s:

* `OAuthUserProvider`, that does nothing but create valid users with default roles (`ROLE_USER` for now) and using `infos_url` in conjunction with `username_path` to provide `getUsername()`'s result. This `UserProvider` is used to represent *remote* OAuth user, when you don't need to do fancy things with your users, such as managing roles and ACLs.
* `EntityUserProvider` is an abstract provider that you need to extend. It fetches users from the database and creates them on the fly if they don't already exist. It requires Doctrine to work.