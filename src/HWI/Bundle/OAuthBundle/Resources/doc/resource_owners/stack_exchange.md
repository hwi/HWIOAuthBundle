Step 2x: Setup Stack Exchange
=============================
First you will have to register your application on Stack Exchange. Check out the
documentation for more information: http://stackapps.com/apps/oauth/register.

Next configure a resource owner of type `stack_exchange` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Stack Exchange documentation
for the [available scopes](https://api.stackexchange.com/docs/authentication#scope).

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                stack_exchange
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
