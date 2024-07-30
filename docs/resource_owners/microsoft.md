Step 2x: Setup Microsoft
===========================
First you will have to register your application on Microsoft. Check out the
documentation for more information: https://learn.microsoft.com/en-us/entra/identity-platform/quickstart-register-app.

Next configure a resource owner of type `microsoft` with appropriate`client_id` and `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                microsoft
            client_id:           <client_id>
            client_secret:       <client_secret>

```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
