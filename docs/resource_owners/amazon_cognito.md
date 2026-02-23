Step 2x: Setup Amazon Cognito
=====================
1. First you will need to creat a user pool on Amazon Cognito https://docs.aws.amazon.com/cognito/latest/developerguide/user-pool-next-steps.html.
2. Next, we need to create an app client for this user pool so that we can use Cognito’s OAuth 2.0 service. Make sure to take note of the `client_id` and `client_secret` as we will need them later. https://docs.aws.amazon.com/cognito/latest/developerguide/user-pool-settings-client-apps.html
3. Add a callback URL `{HOST}/security/cognito/check`.
4. You will also need cognito `domain` and `region` (found in Amazon Cognito)

Next configure a resource owner of type `amazon_cognito` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Amazon documentation
for the available scopes.

``` yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                amazon_cognito
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "email openid" #needs to be enabled in cognito (profile, phone)
            options:
                region: <pool_region>
                domain: <pool_domain> // or <custom_domain> like https://yoursite.com 
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
