Step 2x: Setup TikTok
=====================
First you will have to register your application on TikTok for Developers and
add the Login Kit product to it. Check out the documentation for more
information: https://developers.tiktok.com/doc/login-kit-web.

You can create your application here: https://developers.tiktok.com/apps.

Next configure a resource owner of type `tiktok` with appropriate `client_id`
(TikTok calls it the client key) and `client_secret`. The scope defaults to
`user.info.basic`; for the available scopes you should check the official
TikTok documentation: https://developers.tiktok.com/doc/tiktok-api-scopes.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                tiktok
            client_id:           <client_key>
            client_secret:       <client_secret>
```

The user info request names the fields it wants, `open_id`, `union_id`,
`avatar_url` and `display_name` by default. When your scopes grant more, list
them with the `fields` option:

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                tiktok
            client_id:           <client_key>
            client_secret:       <client_secret>
            scope:               'user.info.basic,user.info.profile'
            options:
                fields:          'open_id,union_id,avatar_url,display_name,username,profile_deep_link'
```

Note that the user identifier is the `open_id`, which TikTok scopes to your
application: the same user gets another one in each of your applications.

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
