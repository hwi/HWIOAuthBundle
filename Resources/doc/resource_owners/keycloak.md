Step 2x: Keycloak
===============
First, create your client application in Keycloak, and set 'Access type' to 'confidential'. After saving your new client the secret can be found under 'Credentials'. Add these infos along with your realm name (should not be 'master') to a new package config.

Your Keycloak-URL should look like https://myfancykeycloak.example.com/auth. This is your base URL. Authorization-, token- and userinfo-URLS are derived automatically. If for some reason they do not conform with default Keycloak behavior, they can also be set manually.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        keycloak:
            type:                keycloak
            base_url:            <keycloak_url>
            realm:               <realm_name>
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
