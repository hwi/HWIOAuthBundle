Step 2x: Setup Azure
====================
First you will have to register your application with Azure.
Just follow the steps as described here: http://blogs.msdn.com/b/aadgraphteam/archive/2013/05/17/using-oauth-2-0-authorization-code-grant-for-delegated-access-of-directory-via-aad-graph.aspx

More details on the Azure and OAuth can be found here https://msdn.microsoft.com/en-us/library/azure/dn645542.aspx and for Azure Active Directory here https://msdn.microsoft.com/en-us/library/azure/hh974476.aspx

Next configure a resource owner of type `azure` with appropriate `client_id`,
`client_secret`, and `resource`. You can also specify which `application` it
should target (`common` by default)

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:          azure
            client_id:     <client_id>
            client_secret: <client_secret>

            options:
                resource:    https://graph.windows.net
                application: common
```

When you're done, continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
