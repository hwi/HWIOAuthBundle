Step 2x: Setup Dailymotion
==========================
First you will have to register your application on Dailymotion. Check out the
documentation for more information: http://www.dailymotion.com/doc/api/authentication.html

To add new API Key, you should go to: http://www.dailymotion.com/profile/developer/new

Next configure a resource owner of type `dailymotion` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                dailymotion
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Optionally you can tune how dialog is displaying by changing [`display`](http://www.dailymotion.com/doc/api/authentication.html#dialog-form-factors) option:

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                dailymotion
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                display:         popup # dialog is optimized for popup window
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
