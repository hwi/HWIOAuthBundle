Step 2x: Setup Box
==================
First you will have to register your application on Box. Check out the
documentation for more information: http://developers.box.com/oauth/

To add new API Key, you should go to: https://cloud.box.com/developers/services/edit/

Next configure a resource owner of type `box` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                box
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
