Step 2x: Setup Linkedin
=======================
First you will have to register your application on Linkedin. Check out the
documentation for more information: https://developer.linkedin.com/documents/authentication.

Next configure a resource owner of type `linkedin` with appropriate `client_id`,
`client_secret` and `scope`.
Example of values for scope: `r_basicprofile`, `r_emailaddress`, `r_fullprofile` 
as described by [Linkedin API](https://developer.linkedin.com/documents/profile-fields)

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:           linkedin
            client_id:      <client_id>
            client_secret:  <client_secret>
            scope:          <scope>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
