Step 2: Configuring resource owners
===================================
HWIOAuthBundle creates a dedicated service for each resource owner you want to
use in your application. These resource owners will be used in the oauth
firewall. The bundle ships several pre-configured resource owners that need
only a little configuration.

To make this bundle work you need to add the following to your app/config/config.yml:

```yaml
# app/config/config.yml

hwi_oauth:
    # name of the firewall in which this bundle is active, this setting MUST be set
    firewall_name: secured_area

    # an optional setting to configure a query string parameter which can be used to redirect
    # the user after authentication, e.g. /connect/facebook?_destination=/my/destination will
    # redirect the user to /my/destination after facebook authenticates them.  If this is not
    # set then the user will be redirected to the original resource that they requested, or
    # the base address if no resource was requested.  This is similar to the behaviour of
    # [target_path_parameter for form login](http://symfony.com/doc/2.0/cookbook/security/form_login.html).
    # target_path_parameter: _destination

    # here you will add one (or more) configurations for resource owners
    # and other settings you want to adjust in this bundle, just checkout the list below!
```

##### Built-in resource owners:

- [37signals](resource_owners/37signals.md)
- [Amazon](resource_owners/amazon.md)
- [Bitbucket](resource_owners/bitbucket.md)
- [Bitly](resource_owners/bitly.md)
- [DeviantArt](resource_owners/deviantart.md)
- [Disqus](resource_owners/disqus.md)
- [Dropbox](resource_owners/dropbox.md)
- [Eventbrite](resource_owners/eventbrite.md)
- [Facebook](resource_owners/facebook.md)
- [Flickr](resource_owners/flickr.md)
- [Foursquare](resource_owners/foursquare.md)
- [GitHub](resource_owners/github.md)
- [Google](resource_owners/google.md)
- [Instagram](resource_owners/instagram.md)
- [Jira](resource_owners/jira.md)
- [Linkedin](resource_owners/linkedin.md)
- [Mail.ru](resource_owners/mailru.md)
- [Odnoklassniki](resource_owners/odnoklassniki.md)
- [QQ](resource_owners/qq.md)
- [Salesforce](resource_owners/salesforce.md)
- [SensioLabs Connect](resource_owners/sensio_connect.md)
- [Sina Weibo](resource_owners/sina_weibo.md)
- [Stack Exchange](resource_owners/stack_exchange.md)
- [Stereomood](resource_owners/stereomood.md)
- [Trello] (resource_owners/trello.md)
- [Twitch] (resource_owners/twitch.md)
- [Twitter] (resource_owners/twitter.md)
- [Vkontakte](resource_owners/vkontakte.md)
- [Windows Live](resource_owners/windows_live.md)
- [Yahoo](resource_owners/yahoo.md)
- [Yandex](resource_owners/yandex.md)
- [Others](resource_owners/others.md)

### Continue to the next step!
When you're done. Continue by configuring the security layer.


[Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
