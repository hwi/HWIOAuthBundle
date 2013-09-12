Step 2x: Setup mail.ru
============================
First you will have to register your application on Mail.ru. Check out the
documentation for more information: http://api.mail.ru/docs/guides/oauth/sites/.

Next configure a resource owner of type `mailru` with appropriate
`client_id`, `client_secret` and `scope` (optional).

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                mailru
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Scopes are separate by semicolon, you can those scopes: `photos`, `guestbook`, `stream`, `messages`, `events`.

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
