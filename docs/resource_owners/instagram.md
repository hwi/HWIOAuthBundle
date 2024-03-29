Step 2x: Setup Instagram
========================
First you will have to register your application on Instagram. Check out the
documentation for more information: https://developers.facebook.com/docs/instagram-basic-display-api/getting-started

Your OAuth redirect_uri looks like: https://yourwebsite.com/login/service/instagram
(depending on your `routing.yml` documentation)

Next configure a resource owner of type `instagram` with appropriate
`client_id`, `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                instagram
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
