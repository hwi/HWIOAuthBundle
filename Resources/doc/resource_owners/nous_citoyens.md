Step 2x: Setup NousCitoyens
========================

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                nous_citoyens
            client_id:           <client_id>
            client_secret:       <client_secret>
```

Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
