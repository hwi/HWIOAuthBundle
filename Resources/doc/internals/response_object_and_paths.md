Internals: Response object & "paths"
====================================
Response object is an internal class that implements the
[`UserResponseInterface`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/Response/UserResponseInterface.php)
interface, it's a helper class, that allows you (and the bundle itself) to easily access
specific data returned from resource owners as a user content.

By default, for every built-in resource owner as well as for generic purpose, HWIOAuthBundle
provides shortcuts, that help bundle translate response into correct fields in response
object, they are called "paths".

But enough theory, here is example how to fetch user email & picture while using Facebook!

> __Note__: to have this example working properly you need to have properly configured
> HWIOAuthBundle and at least the Facebook resource owner.

```yaml
# app/config/config.yml

hwi_oauth:
    firewall_names:        [secured_area]
    resource_owners:
        facebook:
            type:          facebook
            client_id:     <client_id>
            client_secret: <client_secret>
            scope:         "email"
            infos_url:     "https://graph.facebook.com/me?fields=id,name,email,picture.type(square)"
            paths:
                email:          email
                profilepicture: picture.data.url
```

Yep, that's all! Now you can receive require details in i.e. `loadUserByOAuthUserResponse(UserResponseInterface $response)`:

```php
/* @var $response \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface */
var_dump(
    $response->getEmail(),
    $response->getProfilePicture()
);
```

But sometimes you want to receive some specific details that provide resource owner, here we
show you how to do that using [LinkedIn resource owner](../resource_owners/linkedin.md) as example:

Let's say that your application requires such details as number of received recommendations
for user that is being authenticated, to do this you will need to overwrite default `infos_url`
as described below:

```yaml
# app/config/config.yml
hwi_oauth:
   firewall_names:        [secured_area]
   resource_owners:
       linkedin:
           type:          linkedin
           client_id:     <client_id>
           client_secret: <client_secret>
           scope:         r_fullprofile
           infos_url:     "http://api.linkedin.com/v1/people/~:(id,formatted-name,recommendations-received)"
```

Again the details can be accessed in i.e. `loadUserByOAuthUserResponse(UserResponseInterface $response)`:

```php
/* @var $response \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface */
$data = $response->getResponse(); /* this method will return all data that was sent from resource owner */

var_dump(
   $data['recommendations-received']
);
```

Our path api is so flexible that you can merge individual fields returned in response from resource
owner into one! Check how this could look:

```yaml
# app/config/config.yml
hwi_oauth:
   firewall_names:        [secured_area]
   resource_owners:
       linkedin:
           type:          vkontakte
           client_id:     <client_id>
           client_secret: <client_secret>
           paths:
               realname:  ["first_name", "last_name"]
```

Now just check the results:

```php
/* @var $response \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface */
var_dump(
    $response->getRealName()
);
```

That's all! In this way you can overwrite any parameter of built-in resource owners
and adjust them to your needs. For more details about configuration check
["Reference configuration"](../internals/reference_configuration.md) chapter.

[Return to the index.](../index.md)
