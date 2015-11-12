Step 2x: Setup DISQUS
=====================
First you will have to register your application on DISQUS. Check out the
documentation for more information: http://disqus.com/api/applications/.

Next configure a resource owner of type `disqus` with appropriate
`client_id`, `client_secret` and `scope`. For the available scopes
please check the [documentation](http://disqus.com/api/docs/permissions/).

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                disqus
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "read,write"
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
