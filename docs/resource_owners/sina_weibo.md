Step 2x: Setup Sina Weibo
=========================
First you will have to register your application on Sina Weibo. Check out the
documentation for more information: http://open.weibo.com/

To add new API Key, you should go to: http://open.weibo.com/connect

Next configure a resource owner of type `sina_weibo` with appropriate
`client_id`, `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                sina_weibo
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
