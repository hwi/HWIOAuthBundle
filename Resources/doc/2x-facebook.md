Step 2x: Setup Facebook
=======================
First you will have to register your application on Facebook. Check out the
documentation for more information: http://developers.facebook.com/docs/authentication/.

Next configure a resource owner of type `facebook` with appropriate
`client_id`, `client_secret` and `scope`. Refer to the Facebook documentation
for the available scopes.

``` yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                facebook
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               ""
            #If you want to get extended information about you, such as the profile picture
            #user_response_class: HWI\Bundle\OAuthBundle\OAuth\Response\AdvancedPathUserResponse
```

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
