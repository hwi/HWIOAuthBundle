# KnpOAuthBundle

## Cookbooks

These cookbooks may or may not be specific to this bundle, but they are use-cases that are likely to be encountered, and for which the solution might not be obvious at the moment.

### Requiring full authentication on part of your application

This is the [KnpBundles](http://knpbundles.com/) setup. Basically, you can browse most of the website anonymously, but you need full authentication for a few actions (registering a bundle, indicating that you recommend a bundle, etc). Here's the corresponding security configuration (this is the actual KnpBundles security configuration):

    security:
        firewalls:
            secured_area:
                anonymous:   true
                pattern:     ^/
                oauth:
                    oauth_provider:   github
                    client_id:        %knp_bundles.github.client_id%
                    secret:           %knp_bundles.github.client_secret%
                    scope:            ~
                    check_path:       /oauth/github
                    login_path:       /login
                    failure_path:     /
                logout:
                    path:   /logout
                    target: /

        access_control:
            - { path: ^/add, roles: ROLE_USER }
            - { path: change-usage-status$, roles: ROLE_USER }

        providers:
            secured_area:
                oauth_entity:
                    class: KnpBundlesBundle:User
                    property: name

The key here is the `access_control` section. We define a firewall on the entire website that allows anonymous connections (anonymous users are given the `IS_AUTHENTICATED_ANONYMOUSLY` and that's all) so that everyone can browser the site, but still be authenticated in the Symfony2 meaning of the term.

Then we need to tell Symfony that we want extra-security for some paths, using the `access_control`. We require the `ROLE_USER` role, thus forcing already anonymously authenticated user to re-authenticate to gain higher access privileges.