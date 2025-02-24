Step 2x: Setup Spotify
=====================
First you will have to register your application on Spotify. Check out the
documentation for more information: [authorization guide](https://developer.spotify.com/web-api/authorization-guide/).

Next configure a resource owner of type `spotify` with appropriate
`client_id`, `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                spotify
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Optionally you can force the user to approve the app again if they've already done so with the [`show_dialog`](https://developer.spotify.com/documentation/web-api/tutorials/code-flow) option:

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                spotify
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                show_dialog:     true # Can be false or true
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
