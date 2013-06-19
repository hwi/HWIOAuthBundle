Step 2x: Setup Flickr
=====================
First you will have to register your application in Flickr's ["App Garden"](http://www.flickr.com/services/apps/create/).

Next configure a resource owner of type `flickr` with appropriate `client_id` and `client_secret`.

__Note__: When authorizing your request `perms` parameter is appended to relevant URL with default value being `read`.
Please, refer to [documentation](http://www.flickr.com/services/api/auth.oauth.html) for more information.

``` yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:          flickr
            client_id:     <client_id>
            client_secret: <client_secret>
```

When you're done. Continue by configuring the security layer or go back to setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
