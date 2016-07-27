Step 2x: Setup Clever
=======================
First you will have to register your application on
[Clever](https://clever.com/developers). Check out the documentation for more 
information: https://dev.clever.com/instant-login/bearer-tokens.

Next configure a resource owner of type `clever` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Clever documentation
for the available scopes.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                clever
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
