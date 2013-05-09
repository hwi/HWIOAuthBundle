Step 2x: Setup Odnoklassniki
========================
First you will have to register your application on Odnoklassniki. Check out the
documentation for more information: http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=13992188.

Next configure a resource owner of type `odnoklassniki` with appropriate
`client_id`, `client_secret`, `scope` and `application_key`.

``` yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                odnoklassniki
            client_id:           %odnoklassniki_app_id%
            client_secret:       %odnoklassniki_app_secret%
            scope:               ""
            options:
                odnoklassniki_app_key:    "%odnoklassniki_app_key%"
```

``` yaml
# app/config/parameters.yml

parameters:
    odnoklassniki_app_id:     <client_id>
    odnoklassniki_app_secret: <client_secret>
    odnoklassniki_app_key:    <app_key>

```

You can to use this scope: VALUABLE ACCESS, SET STATUS, PHOTO CONTENT
Scope separate by semicolon.
Its important! You can use users.getLoggedInUser & users.getCurrentUser without scope. It's unbelievable but if you need
some more, you must write letter to oauth@odnoklassniki.ru for give you permission to use scope!
More information about [Odnoklassniki OAuth2 authorization](http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=12878032)

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
