Internals: Reference configuration
==================================

```yaml
# app/config.yml

hwi_oauth:
    # configuration of oauth resource owners to use
    resource_owners:
        github:
            type:                github
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "user:email"
            options:
                csrf:            true

        google:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "https://www.googleapis.com/auth/userinfo.profile"
            user_response_class: \Our\Custom\Response\Class
            paths:
                email:           email
                profilepicture:  picture
            options:
                access_type:     offline

        facebook:
            type:                facebook
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               ""

        my_custom_oauth2:
            type:                oauth2
            client_id:           <client_id>
            client_secret:       <client_secret>
            access_token_url:    https://path.to/oauth/v2/token
            authorization_url:   https://path.to/oauth/v2/authorize
            infos_url:           https://path.to/api/user
            scope:               "user_details"
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
            paths:
                identifier: id
                nickname:   username
                realname:   fullname

        my_custom_oauth1:
            type:                oauth1
            client_id:           <client_id>
            client_secret:       <client_secret>
            request_token_url:   https://path.to/oauth/v1/requestToken
            access_token_url:    https://path.to/oauth/v1/token
            authorization_url:   https://path.to/oauth/v1/authorize
            infos_url:           https://path.to/api/user
            realm:               "whatever"
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
            paths:
                identifier: id
                nickname:   username

    # list of firewall names the oauth bundle is active in
    firewall_names: [secured_area]

    # optional target_path_parameter to provide an explicit return URL
    #target_path_parameter: _destination

    # use referer as fallback to determine default return URL
    #use_referer: true

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
#        confirmation: true # should show confirmation page or not
#        registration_form_handler: my_registration_form_handler
#        registration_form: my_registration_form
#        account_connector: my_link_provider # can be the same as your user provider

    # optional HTTP Client configuration
    http_client:
        timeout:       5
        verify_peer:   true
        ignore_errors: true
        max_redirects: 5

    # allows to switch templating engine for bundle views
    #templating_engine: "php"

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

```yaml
# app/config/routing.yml

hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /connect

facebook_login:
    path: /login/check-facebook

google_login:
    path: /login/check-google

custom_login:
    path: /login/check-custom

github_login:
    path: /login/check-github
```

[Return to the index.](../index.md)
