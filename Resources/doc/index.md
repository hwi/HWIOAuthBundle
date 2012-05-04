Getting Started With HWIOAuthBundle
=====================================

## Installation

1. [Setting up the bundle](1-setting_up_the_bundle.md)
2. [Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
3. [Configuring the security layer](3-configuring_the_security_layer.md)

## Todo

- "connect" documentation
- fosub integration documentation

## Reference configuration

``` yaml
# app/config.yml

hwi_oauth:
    # configuration of oauth resource oweners to use
    resource_owners:
        github:
            type:                github
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               ""
        google:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               ""
            user_response_class: "\Our\Custom\Response\Class"
            paths:
                email: email
                profilepicture: picture

        facebook:
            type:                facebook
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               ""

        my_custom_provider:
            type:                generic
            client_id:           <client_id>
            client_secret:       <client_secret>
            access_token_url:    https://path.to/oauth/v2/token
            authorization_url:   https://path.to/oauth/v2/authorize
            infos_url:           https://path.to/api/user
            scope:               ""
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse
            paths:
                username: id
                displayname: username
                email: email

    # name of the firewall the oauth bundle is active in
    firewall_name: secured_area

    # optional FOSUserBundle integration
    fosub:
        # try 30 times to check if a username is available (foo, foo1, foo2 etc)
        username_iterations: 30

        # mapping between resource owners (see below) and properties
        properties:
            github: githubId
            google: googleId
            facebook: facebookId
            my_custom_provider: customId

    # if you want to use 'connect' and do not use the FOSUB integration, configure these separately
    connect: ~
#        registration_form_handler: my_registration_form_handler
#        registration_form: my_registration_form
#        connect_provider: my_link_provider # can be the same as your user provider
```

``` yaml
# app/config/security.yml
security:
    providers:
        fos_userbundle:
            id: fos_user.user_manager

    firewalls:
        secured_area:
            pattern:    ^/
            form_login:
                provider: fos_userbundle
                login_path: /connect/
                check_path: /login/login_check
            anonymous:    true
            oauth:
                resource_owners:
                    github:             "/login/check-github"
                    google:             "/login/check-google"
                    facebook:           "/login/check-facebook"
                    my_custom_provider: "/login/check-custom"
                login_path:        /connect
                failure_path:      /connect

                # FOSUB integration
                oauth_user_provider:
                    service: hwi_oauth.user.provider.fosub_bridge
```
