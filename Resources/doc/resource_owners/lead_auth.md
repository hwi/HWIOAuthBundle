Step 2x: Setup Lead Auth
========================
You have to create an account in https://lead-auth.com and create a PHP application.

Next configure a resource owner of type `lead_auth` with appropriate `client_id`,
`client_secret` and `account`.

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:           lead_auth
            account:        <account>
            client_id:      <client_id>
            client_secret:  <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
