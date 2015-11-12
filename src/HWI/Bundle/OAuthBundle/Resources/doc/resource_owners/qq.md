Step 2x: Setup QQ
=================
First you will have to register your application on QQ. Check out the
documentation for more information: http://connect.qq.com

To add new API Key, you should go to: http://connect.qq.com/intro/login

Next configure a resource owner of type `qq` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                qq
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
