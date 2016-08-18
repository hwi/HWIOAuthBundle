Internals: Configuring an external Resource Owner
=================================================
This bundle has tons of built-ins Resource Owners (Github, Trello, Google, etc..) that extends
[`GenericOAuth2ResourceOwner`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/ResourceOwner/GenericOAuth2ResourceOwner.php) and [`GenericOAuth1ResourceOwner`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/ResourceOwner/GenericOAuth1ResourceOwner.php).

This is idyllic because with these ones, you just need to give basically a `client_id`,
`client_secret` and a few other configuration values and you are up and running with these.
Furthermore, these Resource Owners implements many useful methods like `refreshAccessToken` or
`revokeAccessToken` that might be handy in your projects!

You can even rely directly on the generic Resource Owners in your config, adding 3 more parameters,
`access_token_url`, `authorization_url`, `infos_url`, and `authorization_url` for OAuth1 and
`scope` for `OAuth2`. A little bit of configuration overhead, but in a few more minutes, you are
up and running with these.

But (cuz there always is a but...) in some cases (too many of them unfortunately), providers does not strictly follow OAuth1 or OAuth2 standards and you cannot use a generic Resource Owner like
mentioned above.

For example, look at [`WunderlistResourceOwner`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/ResourceOwner/WunderlistResourceOwner.php), we needed to override `doGetUserInformationRequest`
because they uses some cryptic headers names.

Good news, you could do so in your project, without having to endure all the submission and pull
request process here on the bundle to define your own Resource Owners (but a nice PR is still
appreciated though if it could be useful for other. But __at least__, you can implement it quickly
on your side without having to wait that we merge it here).

To do so, you'll just have to create your Resource Owner class in your project, extending either
[`GenericOAuth2ResourceOwner`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/ResourceOwner/GenericOAuth2ResourceOwner.php) or [`GenericOAuth1ResourceOwner`](https://github.com/hwi/HWIOAuthBundle/blob/master/OAuth/ResourceOwner/GenericOAuth1ResourceOwner.php).


```php
<?php

namespace You\Namespace;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;

class YourResourceOwner extends GenericOAuth2ResourceOwner
{
    [...]
}
```

And then in your Resource Owners configuration:

```yaml
resource_owners:
  your_provider:
    type: oauth2
    class: You\Namespace\YourResourceOwner
    client_id: %oauth.your_provider.client_id%
    client_secret: %oauth.your_provider.client_secret%
    scope: %oauth.your_provider.scope%
```

That's all folks!

[Return to the index.](../index.md)

