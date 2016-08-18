Step 2x: Setup itembase
=======================
After you obtained a client id and client secret from itembase (see
[here](http://itembase.github.io/#steps-to-get-started) how to do that), you
are ready to use HWIOAuthBundle to integrate OAuth2 flow.

Configure a resource owner of type `itembase` with appropriate `client_id`,
`client_secret` and `scope` (more information can be found in
[the documentation](http://itembase.github.io/#1.-select-the-scope-of-your-client-application)).

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        some_name:
            type:                itembase
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               <scope_string>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
