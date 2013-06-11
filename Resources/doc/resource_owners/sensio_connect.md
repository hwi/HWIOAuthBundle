Step 2x: Setup SensioLabs Connect
=================================
First you will have to register your application on [SensioLabs Connect](https://connect.sensiolabs.com/account/app/new).

Next configure a resource owner of type `sensio_connect` with appropriate
`client_id`, `client_secret` and `scope`. All those information will be
visible at edit page for application you just added.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                sensio_connect
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "SCOPE_PUBLIC"
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
