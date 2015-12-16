Step 2x: Setup Twitter
======================
First you will have to [register](https://dev.twitter.com/apps) your application on Twitter. Check out the
documentation for more information: https://dev.twitter.com/docs/auth/oauth.

You must set up a callback url and check "Allow this application to be used to Sign in with Twitter".

Next configure a resource owner of type `twitter` with appropriate
`client_id`, `client_secret`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                twitter
            client_id:           <consumer-key>
            client_secret:       <consumer-secret>
```

When you want to request a user’s email address permission use
the optional option [`include_email`].

Twitter documentation:
- https://dev.twitter.com/rest/reference/get/account/verify_credentials

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                twitter
            client_id:           <consumer-key>
            client_secret:       <consumer-secret>
            options:
                include_email: true
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
