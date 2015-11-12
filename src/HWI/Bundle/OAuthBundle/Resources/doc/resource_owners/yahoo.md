Step 2x: Setup Yahoo
=======================
First you will have to register your application on Yahoo ("Create a Project"). Check out the
documentation for more information: http://developer.yahoo.com/oauth/.

The Yahoo Resource Owner uses the Yahoo Profile API to get user information, so when setting up your Yahoo Project
you must ensure that you have enabled access to the "Social Directory" service, with at least "Read Public" access
under the "Social Directory (Profiles)" section.

Next configure a resource owner of type `yahoo` with appropriate `client_id`,
`client_secret`.

```yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                yahoo
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
