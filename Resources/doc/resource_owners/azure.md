Step 2x: Setup Azure
====================
First you will have to register your application with Azure.
Just follow the steps as described here: https://docs.microsoft.com/en-us/azure/active-directory/saas-apps/openidoauth-tutorial

More details on the Azure and OAuth can be found here https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-v2-protocols

Next configure a resource owner of type `azure` with appropriate `client_id`,
`client_secret`. You can also specify which `application` it
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
                application: common
```

When you're done, continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
