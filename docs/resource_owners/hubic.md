Step 2x: Setup Hubic
======================
First you will have to register your application on Hubic web application. You could add your application inside [account parameters](https://hubic.com/home/browser/developers/) (you must log in before).

Next configure a resource owner of type `hubic` with appropriate
`client_id`, `client_secret`.

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        hubic:
            type:                hubic
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
