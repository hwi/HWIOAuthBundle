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

- [37signals](2x-37signals.md)
- [Disqus](2x-disqus.md)
- [Dropbox](2x-dropbox.md)
- [Facebook](2x-facebook.md)
- [Flickr](2x-flickr.md)
- [Foursquare](2x-foursquare.md)
- [GitHub](2x-github.md)
- [Google](2x-google.md)
- [Jira](2x-jira.md)
- [Linkedin](2x-linkedin.md)
- [Odnoklassniki](2x-odnoklassniki.md)
- [SensioLabs Connect](2x-sensio_connect.md)
- [Stack Exchange](2x-stack_exchange.md)
- [Twitter] (2x-twitter.md)
- [Vkontakte](2x-vkontakte.md)
- [Windows Live](2x-windows_live.md)
- [Yahoo](2x-yahoo.md)
- [Yandex](2x-yandex.md)
- [Others](2x-others.md)

> Bonus: [Overwriting default configuration](2x-overwriting-defaults.md) of built-in resource owners.

### Continue to the next step!
When you're done. Continue by configuring the security layer.
[Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
