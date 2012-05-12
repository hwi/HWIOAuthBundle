Step 2x: Others
===============
If the resource owners you are looking for isn't implemented in this bundle yet
(implement it and send a PR! ;) ). Alternatively you can configure a general
resource owner:

``` yaml
# app/config.yml

hwi_oauth:
    resource_owners:
        my_custom:
            type:                generic
            client_id:           <client_id>
            client_secret:       <client_secret>
            access_token_url:    https://path.to/oauth/v2/token
            authorization_url:   https://path.to/oauth/v2/authorize
            infos_url:           https://path.to/api/user
            access_token_encode: false
            scope:               ""
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
            paths:
                username: id
                displayname: username
```

Only json is supported for access_token_encode option.

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live, Viadeo and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
