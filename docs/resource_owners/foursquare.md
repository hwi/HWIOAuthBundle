Step 2x: Setup Foursquare
=========================
First you will have to register your application on Foursquare. Check out the
documentation for more information: https://foursquare.com/oauth.

Next configure a resource owner of type `foursquare` with appropriate
`client_id`, `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                foursquare
            client_id:           <client_id>
            client_secret:       <client_secret>
```

For easy integration for making signed calls with your oauth token to the Foursquare REST API try the [JcrollFoursquareApiBundle](https://github.com/jcroll/foursquare-api-bundle).

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
