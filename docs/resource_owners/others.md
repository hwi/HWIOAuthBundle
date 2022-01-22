Step 2x: Others
===============
If the resource owners you are looking for isn't implemented in this bundle yet, you can configure a general
resource owner to use it on your own:

#### OAuth2
```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        my_custom:
            type:                oauth2
            class:               \HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\OAuth2ResourceOwner
            client_id:           <client_id>
            client_secret:       <client_secret>
            access_token_url:    https://path.to/oauth/v2/token
            authorization_url:   https://path.to/oauth/v2/authorize
            infos_url:           https://path.to/api/user
            scope:               "read"
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
            paths:
                identifier: id
                nickname:   username
                realname:   fullname
```

#### OAuth1.0a

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        my_custom:
            type:                oauth1
            class:               \HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\OAuth1ResourceOwner
            client_id:           <client_id>
            client_secret:       <client_secret>
            request_token_url:   https://path.to/oauth/v1/requestToken
            access_token_url:    https://path.to/oauth/v1/token
            authorization_url:   https://path.to/oauth/v1/authorize
            infos_url:           https://path.to/api/user
            realm:               "read"
            user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse
            paths:
                identifier: id
                nickname:   username
                realname:   fullname
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
