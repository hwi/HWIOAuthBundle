Step 2x: Setup Linkedin OpenID
=======================
First you will have to register your application on Linkedin. Check out the
documentation for more information: https://learn.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/sign-in-with-linkedin-v2.

Next configure a resource owner of type `linkedin_openid` with appropriate `client_id`,
`client_secret` and `scope`.
Example of values for scope: `openid`, `profile`, `email`
as described by [Authenticating Members](https://learn.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/sign-in-with-linkedin-v2?source=recommendations#authenticating-members)

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:           linkedin_openid
            client_id:      <client_id>
            client_secret:  <client_secret>
            scope:          <scope>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
