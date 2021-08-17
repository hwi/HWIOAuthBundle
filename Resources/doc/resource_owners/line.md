Step 2x: Setup Line
=========================
First you will have to register your application on Line. Check out the
documentation for more information: https://developers.line.biz/en/docs/line-login/

In order to use Line Login, you will need to create a provide and a channel. The channel ID works as the client ID, while the channel secret works as the client secret. A provider and channel can be created in Line console: https://developers.line.biz/console/

Next configure a resource owner of type `line` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                line
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).