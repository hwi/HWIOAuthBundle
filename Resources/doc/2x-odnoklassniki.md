Step 2x: Setup Odnoklassniki
========================
First you will have to register your application on Odnoklassniki. Check out the
documentation for more information: http://dev.odnoklassniki.ru/wiki/pages/viewpage.action?pageId=13992188.

Next configure a resource owner of type `odnoklassniki` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Vkontakte documentation
for the available [scopes](http://vk.com/developers.php?oid=-17680044&p=Application_Access_Rights).

``` yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                odnoklassniki
            client_id:           %odnoklassniki_app_id%
            client_secret:       %odnoklassniki_app_secret%
            scope:               ""
```

``` yaml
# app/config/parameters.yml

parameters:
    odnoklassniki_app_id:     <client_id>
    odnoklassniki_app_secret: <client_secret>
    odnoklassniki_app_public: <client_public>

```

You can to use this scope: VALUABLE ACCESS, SET STATUS, PHOTO CONTENT
Scope separate by semicolon.
Its important! You can use users.getLoggedInUser & users.getCurrentUser without scope. But if you need
some more, you required write letter to oauth@odnoklassniki.ru

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
