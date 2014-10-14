Step 2x: Setup EVE Online
=====================
First you will have to register your application on EVE: Developers website. Check out the
documentation for more information: https://developers.eveonline.com.

Next configure a resource owner of type `eve` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the EVE: Developers documentation
for the available scopes.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                eve_online
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Optionally, for authenticating to EVE online test servers, you can override endpoint settings:

```yaml
# app/config/config.yml
hwi_oauth:
    resource_owners:
        any_name:
            type:                eve_online
            client_id:           <client_id>
            client_secret:       <client_secret>
            authorization_url:  https://sisilogin.testeveonline.com/oauth/authorize
            access_token_url:   https://sisilogin.testeveonline.com/oauth/token
            infos_url:          https://sisilogin.testeveonline.com/oauth/verify
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
