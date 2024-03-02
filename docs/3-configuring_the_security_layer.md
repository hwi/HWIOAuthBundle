Step 3: Configuring the security layer
======================================

### A) Have a user provider that implements `OAuthAwareUserProviderInterface`

The bundle needs a service that is able to load users based on the user response of the oauth endpoint.

The HWIOAuthBundle also ships with two default implementations:

1. `HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider` (service name: `hwi_oauth.user.provider`) - doesn't persist users,
2. `HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider` (service name: `hwi_oauth.user.provider.entity`) - loads users from a database.

The `$properties` variable expects array of strings, where key is name of the resource owner (defined in `config/packages/security.yaml`, see below),
and value is property name on the entity (i.e. `App\Entity\User`).

This provider requires additional configuration:
```yaml
# config/services.yaml
services:
    hwi_oauth.user.provider.entity:
        class: HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider
        arguments:
            $class: App\Entity\User
            $properties:
                'facebook': 'facebook'
                'google': 'google'
                'my_custom_provider': 'myCustomProvider'
```

3. Implement the interface: `HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface` in custom user provider,

### B) Configure the oauth firewall

In the firewall configuration you will need to configure a login path for the
resource owners you configured in [step 2](../docs/2-configuring_resource_owners.md).
Additionally, you will need to point the oauth firewall to the appropriate service to use for loading users:

In Symfony 5.4 or 6.0+, use:
```yaml
# config/packages/security.yaml
security:
    enable_authenticator_manager: true

    firewalls:
        main:
            pattern: ^/
            oauth:
                resource_owners:
                    facebook:           "/login/check-facebook"
                    google:             "/login/check-google"
                    my_custom_provider: "/login/check-custom"
                    my_github:          "/login/check-github"
                login_path:   /login
                use_forward:  false
                failure_path: /login

                oauth_user_provider:
                    service: my.oauth_aware.user_provider.service

    access_control:
        - { path: ^/login, roles: PUBLIC_ACCESS }
        - { path: ^/connect, roles: PUBLIC_ACCESS }
        - { path: ^/, roles: ROLE_USER } // force login
```

If you use Symfony <5.4:
```yaml
# config/packages/security.yaml
security:
    firewalls:
        main:
            pattern: ^/
            anonymous: ~
            oauth:
                resource_owners:
                    facebook:           "/login/check-facebook"
                    google:             "/login/check-google"
                    my_custom_provider: "/login/check-custom"
                    my_github:          "/login/check-github"
                login_path:   /login
                use_forward:  false
                failure_path: /login

                oauth_user_provider:
                    service: my.oauth_aware.user_provider.service

    access_control:
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
```

The paths configured at the `resource_owners` section should be defined in your routing.

```yaml
# app/config/routing.yml
facebook_login:
    path: /login/check-facebook

google_login:
    path: /login/check-google

custom_login:
    path: /login/check-custom

github_login:
    path: /login/check-github
```

## Add a link in `login.html.twig` to activate the login process
```
<a href="{{ path('hwi_oauth_service_redirect', {'service': 'google' }) }}">
    <span>Login with Google</span>
</a>
```

## That was it!

That's the basic setup of the bundle.

## Going further

If you would like to register user when account was not found in your application, please read [Step 4: Configuring the connect (register) layer](4-configuring_the_connect.md).

If you would like to define own Resource Owner (that are i.e. pretty simple or require third-party code - SDK's), or understand details of how it works under the hood, you should check [Internals](./internals) documentation.

[Return to the index.](index.md)
