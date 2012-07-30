Step 2x: Setup Google
=====================
First you will have to register your application on Google. Check out the
documentation for more information: https://developers.google.com/accounts/docs/OAuth2.

Next configure a resource owner of type `google` with appropriate
`client_id`, `client_secret` and `scope`.

Example `scope` values include:
- https://www.googleapis.com/auth/userinfo.profile
- https://www.googleapis.com/auth/userinfo.email

They are to be space delimited. Refer to the Google documentation for more information.

``` yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile"
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
