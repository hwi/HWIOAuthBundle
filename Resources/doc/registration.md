Registration through social services
====================================

Make sure that the link redirects the registration application in social
networks indicates the route "hwi_oauth_connect", e.g.:

``` yaml
# app/config/routing.yml

# Your routes...

#Redirect link in configuration of the social network: http://<your_site>/connect/
hwi_oauth_login:
    resource: "@HWIOAuthBundle/Resources/config/routing/login.xml"
    prefix: /connect
```

To activate the behavior user registration, if it was not found in the database, do the following:

### 1. Add the following to the routing.yml:

``` yaml
# app/config/routing.yml

#This should be at the top
hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /login

#Other routes...

hwi_oauth_connect:
    resource: "@HWIOAuthBundle/Resources/config/routing/connect.xml"
    prefix: /connect

hwi_oauth_login:
    resource: "@HWIOAuthBundle/Resources/config/routing/login.xml"
    prefix: /connect
```

### 2. Configuration of security

Path of failure logon should to point to the route "hwi_oauth_connect":

``` yaml
# app/config/security.yml

security:

    #some configurations...

    firewalls:
        secured_area:
            #FOSUB
            pattern:    ^/
            form_login:
                provider: fos_userbundle
                csrf_provider: form.csrf_provider
            logout: true
            anonymous: true
            oauth:
                resource_owners:
                    facebook: "/login/check-facebook"
                    twitter: "/login/check-twitter"
                login_path: /login
                #failure path
                failure_path: /connect/
                #integration with FOSUB
                oauth_user_provider:
                    service: hwi_oauth.user.provider.fosub_bridge
```

This is to ensure that in case of a failed login to the site (for example, a user can not be found by key),
it was redirected to the registration form.

### 3. Override the registration form templates (optional)

You can override the registration form templates for your website. To do this, create (or just copy views from
HWIOAuthBundle) views in folder app/Resources/HWIOAuthBundle/views/Connect/registration.html.twig and
app/Resources/HWIOAuthBundle/views/Connect/registration_success.html.twig.