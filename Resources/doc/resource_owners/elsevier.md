Step 2x: Setup Elsevier
=========================
Elsevier oAuth API does not provide user information retrieval, but only
access/refresh token retrieval, to be stored for further API calls.

First you will have to register your application on Elsevier. Check out the
documentation for more information: http://www.developers.elsevier.com/cms/content/sciverse-apis-authentication-oauth.

Next configure a resource owner of type `elsevier` with appropriate
`client_id`, `client_secret` and `scope`.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                elsevier
            client_id:           <API key>
            client_secret:       <elsevier_targetAppName>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
