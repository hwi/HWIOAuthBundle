# KnpOAuthBundle

## Custom OAuth Providers

You can of course implement your own custom OAuth provider. The good news is it's a fairly easy three steps process:

1. Implement the `Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface` interface
2. Declare the corresponding service
3. Configure your firewall

### Implementing the interface

The interface is not too hard to implement, it only consists in three methods:

* `getUsername($accessToken)` must return the user's username.
* `getAuthorizationUrl($loginCheckUrl, array $extraParameters = array())` must return the provider's authorization url.
* `getAccessToken($code, array $extraParameters = array())` must return an access token.

Please see `Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProvider` for an example implementation.

To ease the task even a little more, you can extend the generic `OAuthProvider` provider. This provider comes with a few helper methods:

* `configure()` is the place to put some custom logic (you can see the `GithubProvider` for an example of that).
* `getOption($name)` retrieves an option, with existance check.
* `httpRequest($url, $method)` is a small wrapper around `Buzz`.

### Declaring the DIC service

Once your provider is implemented, you need to declare it as a DIC service. This step is fairly easy too, since Symfony provides a way to declare services without the hassle of creating a DIC Extension. Just define your service in your configuration, under the `services` section:

    services:
        my_bundle.security.oauth.my_provider:
            class: MyBundle\Security\Http\OAuth\MyProvider

See [Symfony's service container documentation](http://symfony.com/doc/current/book/service_container.html#creating-configuring-services-in-the-container) for more information on that.

Bear with me, we're almost done.

### Configuring your firewall

The `KnpOAuthBundle` tries to be clever, and decides that any OAuth provider containing a dot (`.`) is in fact a DIC service that we want to use. The configuration would then be:

    security:
        firewalls:
            secured_area:
                pattern:    ^/secured/
                oauth:
                    oauth_provider:   my_bundle.authentication.entry_point.my_provider
                    client_id:        <your_oauth_client_id>
                    secret:           <your_oauth_secret>
                    scope:            <your_oauth_scope>
                    check_path:       /secured/login_check
                    login_path:       /secured/login
                    failure_path:     /

What options are required is totally up to your provider's implementation, but you will most likely want to pre-configure most of them.

Hurray! You have an OAuth provider! Will you have a [user provider](06_builtin_user_providers.md) with that?