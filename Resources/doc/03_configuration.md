# KnpOAuthBundle

You should already be familiar with [Symfony2's security configuration](http://symfony.com/doc/current/book/security.html). If you are not, now would be a good time to read it.

## Configuration

In its most basic form, using this bundle is just a matter of creating an OAuth firewall with an `oauth` listener in your security configuration. The bundles exposes a number of configuration directives to fine-tune your OAuth usage.

Here's a full-fledged example of configuration:

    security:
        firewalls:
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
                    failure_path:      /
                logout:
                    path: /secured/logout
                    target: /

        providers:
            main:
                oauth_entity:
                    class: MyBundle:User
                    property: username

If you're familiar with the security component, you might have noticed that contrary to the form login listener, we don't disable security on the `login_path`. This is needed for this firewall to work _as expected_ when you hit the login page.

Please note that most of these options are *optional* under certain conditions. Mostly, when you're using a provider that comes pre-configured with them. See the [built-in OAuth providers page](04_builtin_oauth_providers.md) for more information on that.

The `check_path`, `login_path` and `failure_path` are standard Symfony2 security configuration directives.

### oauth_provider

*default*: `oauth`

The `oauth_provider` defines which provider to use. Most OAuth bundles will present you with a set of built-in providers, leaving you no choice of using a custom provider. This bundle is different. It's been built with genericity in mind from the beginning and you can use the `oauth` provider to use virtually any OAuth provider, provided it doesn't break the OAuth specification (for example, you can't use it with github).

Available providers, as of now, are:

* `oauth`
* `github`

### client_id

This is provided by your OAuth provider.

### secret

This is provided by your OAuth provider too.

### scope

The scope of the data you wish to retrieve about your users. You can require multiple scopes by separating them with a space.

### authorization_url

The `authorization_url` is the URL used for the first OAuth round-trip. When initiating the authentication procedure, your users will be redirected there and presented, hopefuly, with the provider's OAuth authorization screen.

### access_token_url

Your provider also exposes an URL to retrieve an `access_token`. This token is used to retrieve information about your users.

### infos_url

This is the URL used to retrieve informations about your user. This may or may not be provided by your OAuth provider. The default OAuth provider expects this URL to return a `json` encoded response. If your provider does not return `json` data, you will have to implement a [custom OAuth provider](05_custom_oauth_providers.md).

Please note that if you set `infos_url`, you must set `username_path` as well.

### username_path

A dot separated path to search `infos_url`'s response for a username. For example, Github returns user information in the form of a `json` structure that looks a bit like that (truncated for clarity):

    { user: { login: 'geoffrey '} }

In that case, the `username_path` would be `user.login`.

Please note that if you set `username_path`, you must set `infos_url` as well.

Now you probably want to know a bit more about [built-in oauth providers](04_builtin_oauth_providers.md).