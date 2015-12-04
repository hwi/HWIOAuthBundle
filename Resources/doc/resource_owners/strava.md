Step 2x: Setup Strava
=====================
First you will have to register your application on Strava. Check out the
documentation for more information: http://strava.github.io/api/.

Next configure a resource owner of type `strava` with appropriate
`client_id`, `client_secret` and `scope`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                strava
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
