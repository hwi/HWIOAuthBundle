Step 2x: Setup 37signals
========================
First you will have to register your application on 37signals on https://integrate.37signals.com/. Check out the
authentication documentation for more information: https://github.com/37signals/api/blob/master/sections/authentication.md

Make sure you register the correct redirect_url (http://yourpoject.com/login/check-37signals) otherwise you'll get an error.

Next configure a resource owner of type `37signals` with appropriate `client_id`,
`client_secret`.

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                37signals
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
