Step 2x: Setup Passage
=====================
First you will have to register your application on Passage. Check out the 
documentation for more information: https://docs.passage.id/hosted-login/creating-a-new-app.

Next configure a resource owner of type `passage` with appropriate
`client_id`, `client_secret` & `options.sub_domain`. For the available scopes (default: `openid email`) you should
check official Passage documentation: https://docs.passage.id/hosted-login/oidc-client-configuration

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:           passage
            client_id:      <client_id>
            client_secret:  <client_secret>
            options:
                sub_domain: <sub_domain>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
