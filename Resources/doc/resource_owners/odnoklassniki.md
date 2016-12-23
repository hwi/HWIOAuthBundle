Step 2x: Setup Odnoklassniki
============================
First you will have to register your application on Odnoklassniki. Check out the
documentation for more information: http://apiok.ru/wiki/pages/viewpage.action?pageId=13992188.

Next configure a resource owner of type `odnoklassniki` with appropriate
`client_id`, `client_secret`, `scope` (optional) and `application_key`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                odnoklassniki
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                application_key: <application_key>
```

Scopes are separate by semicolon, you can those scope: `VALUABLE ACCESS`, `SET STATUS`, `PHOTO CONTENT`.
It's important! You can use `users.getLoggedInUser` & `users.getCurrentUser` without scope. If your application
requires some additional data, you must write an email to `oauth@odnoklassniki.ru` and ask for giving you additional
permissions! More information about [Odnoklassniki OAuth2 authorization](https://apiok.ru/wiki/pages/viewpage.action?pageId=42476522).

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
