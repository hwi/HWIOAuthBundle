Step 2x: Setup GitLab
=====================
First you will have to register your application on GitLab. Check out the
documentation for more information: https://docs.gitlab.com/ee/integration/oauth_provider.html.

Next configure a resource owner of type `gitlab` with appropriate
`client_id` & `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                gitlab
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
