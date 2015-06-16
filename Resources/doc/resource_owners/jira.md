Step 2x: Setup Jira
===================

First you will have to register your application with your
Jira instance. Check out the [Atlassian documentation](https://confluence.atlassian.com/display/JIRA/Configuring+OAuth+Authentication+for+an+Application+Link).

Next configure a resource owner of type `jira` with appropriate
`client_id`, `client_secret` and `base_url`.

The Client Secret should either be a path to the private key pem file.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                jira
            client_id:           <client_id>
            client_secret:       <client_secret>
            # Base URL of your Jira installation with no trailing slash (e.g. https://example.com/jira)
            base_url:            <base_url>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
