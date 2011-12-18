# KnpOAuthBundle, an OAuth firewall for all your Symfony2 OAuth needs.

This is still a Work In Progress, but it basically works.

Still todo:

* ease local user persistance
* implement "oauth providers wrappers" to encapsulate well-known providers configuration
* code-cleanup
* unit tests

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

Add `Buzz` to your autoload:

    $loader->registerNamespaces(array(
        'Buzz'             => __DIR__.'/../vendor/Buzz/lib/'
    ));

Finally, register the bundles in your `AppKernel`:

    $bundles = array(
        new Knp\OAuthBundle\KnpOAuthBundle(),
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

* `authorize_url` is the OAuth authorization URL provided by your OAuth provider.
* `access_token_url` is the OAuth access token url, courtesy of your OAuth provider.
* `infos_url` is the URL where your provider will give you infos about your user.
* `username_path` is the dot-separated array path where to find the username in the response content of `infos_url` (e.g. github returns an array in which `$foo['user']['login']` holds the user's login, so we set `username_path` to `user.login`)
* `client_id` is the... client id, provided by... your OAuth provider.
* `secret`, do I really have to explain? hint: it's provided by your OAuth provider too.
* `scope` is how much information and authorization you wish to access.

The `check_path` and `login_path` directives are standard Symfony2 security configuration. You should read the [security documentation](http://symfony.com/doc/current/book/security.html) if you're not yet familiar with them.

This bundle comes with a custom `UserProvider` that does nothing but create valid users with default roles (`ROLE_USER` for now) and using `infos_url` in conjunction with `username_path` to provide `getUsername()`'s result.

If you don't need to manage roles or ACLs for your users, then you're set.