Step 2x: Setup Windows Live
===========================
First you will have to register your application on Windows Live. Check out the
documentation for more information: https://msdn.microsoft.com/en-us/windows/desktop/hh243647.

Next configure a resource owner of type `windows_live` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Windows Live
documentation for the available scopes.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                windows_live
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               wl.signin   

```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
