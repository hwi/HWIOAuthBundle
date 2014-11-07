Step 2x: Setup PayPal
=====================
First you will have to register your application on PayPal. Check out the
documentation for more information: [manage apps](https://developer.paypal.com/docs/integration/admin/manage-apps/).

The application should, at least, have the "Basic authentication" and "Email address" capabilities.
See "Advanced options" in your [app configuration](https://developer.paypal.com/webapps/developer/applications)

Next configure a resource owner of type `paypal` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                paypal
            client_id:           <client_id>
            client_secret:       <client_secret>
```

You can also access to the sandbox while not in production:

```yaml
# app/config/config_dev.yml

hwi_oauth:
    resource_owners:
        any_name: { options: { sandbox: true } }
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
