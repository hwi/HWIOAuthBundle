# KnpOAuthBundle, an OAuth firewall for all your Symfony2 OAuth needs.

## Installation

This bundle depends on Buzz, the lightweight HTTP client by awesome @kriswallsmith, so add it to your project's deps file alongside the bundle itself. Also, since we're lazy, we'll use sensiolabs' `BuzzBundle` by no less awesome @marcw:

    [Buzz]
        git=https://github.com/kriswallsmith/Buzz.git
        version=v0.5
    [BuzzBundle]
        git=https://github.com/sensio/SensioBuzzBundle.git
        target=/bundles/Sensio/BuzzBundle
    [KnpOAuthBundle]
        git=https://github.com/KnpLabs/KnpOAuthBundle.git
        target=/bundles/Knp/OAuthBundle

Then run the usual `bin/vendors`:

    bin/vendors install

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
                    entry_point:   https://github.com/login/oauth/authorize
                    client_id:     <your_oauth_client_id>
                    secret:        <your_oauth_secret>
                    scope:         <your_oauth_scope>
                    check_path:    /secured/login_check
                    login_path:    /secured/login

Here's a quick description of what each directive means:

* `entry_point` is the OAuth authorization URL provided by your OAuth provider.
* `client_id` is the... client id, provided by... your OAuth provider.
* `secret`, do I really have to explain? hint: it's provided by your OAuth provider too.
* `scope` is how much information and authorization you wish to access.

The `check_path` and `login_path` directives are standard Symfony2 security configuration. You should read the [security documentation](http://symfony.com/doc/current/book/security.html) if you're not yet familiar with them.

If you don't need to manage roles or ACLs for your users, then you're set.