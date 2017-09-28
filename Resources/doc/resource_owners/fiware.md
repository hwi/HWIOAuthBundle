Step 2x: Setup FI-WARE
=========================

First you will have to register your application on your FI-WARE Identity Management - KeyRock. Check out the
documentation for more information: 
- http://catalogue.fiware.org/enablers/identity-management-keyrock
- https://github.com/ging/fi-ware-idm/wiki/Using-the-FI-LAB-instance

For testing purpose you could use the configured instance in the FI-WARE Lab Cloud: https://account.lab.fiware.org 

Next configure a resource owner of type `fiware` with appropriate
`client_id`, `client_secret` and `base_url`.

The `base_url` for the FI-WARE Lab Cloud is "https://account.lab.fiware.org". 

If you are using the FI-WARE Lab Cloud you will have to increase the timeout for the http_client from 5 to at least 15 seconds.

```yaml
# app/config/config.yml

# With php-http/httplug-bundle
httplug:
    clients:
        default:
            factory: 'httplug.factory.guzzle6'
            config:
                timeout: 15

hwi_oauth:
    resource_owners:
        any_name:
            type:                fiware
            base_url:            <base_url>
            client_id:           <client_id>
            client_secret:       <client_secret>
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others)](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
