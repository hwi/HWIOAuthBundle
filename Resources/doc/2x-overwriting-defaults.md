Step 2x: Overwriting default configuration
==========================================
HWIOAuthBundle provides wide range of built-in resource owners with default configuration
that covers most of needs while authenticating users. But sometimes you want to receive
some specific details that provide resource owner, here we show you how to do that
using LinkedIn resource owner as example:

Let's say that your application requires such details as number of received recommendations
for user that is being authenticated, to do this you will need to overwrite default `infos_url`
as described below:

```yaml
# app/config/config.yml
hwi_oauth:
    firewall_name: main
    resource_owners:
        linkedin:
            type:                linkedin
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               r_fullprofile
            infos_url:           'http://api.linkedin.com/v1/people/~:(id,formatted-name,recommendations-received)'
```

Now you can receive require details in i.e. `loadUserByOAuthUserResponse(UserResponseInterface $response)`:

```php
/* @var $response \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface */
$data = $response->getResponse();

var_dump(
    $data['recommendations-received']
);
```

That's all! In this way you can overwrite any parameter of built-in resource owners
and adjust them to your needs. For more details about configuration check ["Reference configuration"](reference_configuration.md)
chapter.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](3-configuring_the_security_layer.md).