Step 2x: Setup Discord
======================
First you will have to register your application on Discord. Check out the
documentation for more information: https://discord.com/developers/docs/topics/oauth2.

You can create your application here: https://discord.com/developers/applications.

Next configure a resource owner of type `discord` with appropriate
`client_id`, `client_secret` & `scope`. For the available scopes you should
check the official Discord documentation:
https://discord.com/developers/docs/topics/oauth2#shared-resources-oauth2-scopes

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                discord
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               'identify email'
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
