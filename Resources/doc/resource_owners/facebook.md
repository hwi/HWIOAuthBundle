Step 2x: Setup Facebook
=======================
First you will have to register your application on Facebook. Check out the
documentation for more information: http://developers.facebook.com/docs/authentication/.

Next configure a resource owner of type `facebook` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Facebook documentation
for the available scopes.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                facebook
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Optionally you can tune how dialog is displaying by changing [`display`](https://developers.facebook.com/docs/reference/dialogs/#display) option

Use the optional key [`auth_type`] when you want to re-request a declined permission.
Only supports one value: rerequest.
Facebook documentation:
- https://developers.facebook.com/docs/facebook-login/login-flow-for-web/v2.0#re-asking-declined-permissions
- https://developers.facebook.com/docs/reference/javascript/FB.login/

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                facebook
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                display: popup #dialog is optimized for popup window
                auth_type: rerequest # Re-asking for Declined Permissions
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

> Bonus: Add [Facebook Connect](../bonus/facebook-connect.md) functionality.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
