Step 2x: Setup Yandex
=====================
First you will have to register your application on Yandex here: https://oauth.yandex.ru/client/new.

Next configure a resource owner of type `yandex` with appropriate `client_id` and `client_secret`.
You don't need a scope, because it's a value you set while register your app.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                yandex
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
