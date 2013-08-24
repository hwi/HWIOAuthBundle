Step 2x: Setup Mail.ru
=================================
First you will have to register your application on [Mail.ru](http://api.mail.ru/sites/my/add/).

Next configure a resource owner of type `mailru` with appropriate
`client_id`, `client_secret` and `client_private`. Note: unlike others, `client_private`
must be defined under the `options` section. All those information will be visible at
edit page for application you just added.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                mailru
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                client_private: <client_private>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
