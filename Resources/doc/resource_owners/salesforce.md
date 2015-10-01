Step 2x: Setup Salesforce
=======================
First you will have to register your application on Salesforce. Check out the
documentation for more information: https://help.salesforce.com/help/doc/en/remoteaccess_oauth_web_server_flow.htm

**Please note that Salesforce requires your callback url to be in HTTPS.**

Next configure a resource owner of type `salesforce` with appropriate
`client_id` and `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                salesforce
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
