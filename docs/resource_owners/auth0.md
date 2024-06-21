Step 2x: Setup Auth0
=======================
You have to create an account in http://www.auth0.com and create a PHP application.

Next configure a resource owner of type `auth0` with appropriate `client_id`,
`client_secret`, `domain` and `scope`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:             auth0
            client_id:        <client_id>
            client_secret:    <client_secret>
            base_url:         https://<domain>
            scope:            <scope>
```

Optionally, you can configure the `organization` and `audience` options when the login flow for the application requires this:
```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:             auth0
            client_id:        <client_id>
            client_secret:    <client_secret>
            base_url:         https://<domain>
            scope:            <scope>
            options:
                organization: <organization>
                audience:     <audience>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
