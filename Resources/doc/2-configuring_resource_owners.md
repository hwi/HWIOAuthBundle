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
    # list of names of the firewalls in which this bundle is active, this setting MUST be set
    firewall_names: [secured_area]

    # an optional setting to configure a query string parameter which can be used to redirect
    # the user after authentication, e.g. /connect/facebook?_destination=/my/destination will
    # redirect the user to /my/destination after facebook authenticates them.  If this is not
    # set then the user will be redirected to the original resource that they requested, or
    # the base address if no resource was requested.  This is similar to the behaviour of
    # [target_path_parameter for form login](http://symfony.com/doc/2.0/cookbook/security/form_login.html).
    # target_path_parameter: _destination

    # an optional setting to use the HTTP REFERER header to be used in case no
    # previous URL was stored in the session (i.e. no resource was requested).
    # This is similar to the behaviour of
    # [using the referring URL for form login](http://symfony.com/doc/2.0/cookbook/security/form_login.html#using-the-referring-url).
    # use_referer: true

    # here you will add one (or more) configurations for resource owners
    # and other settings you want to adjust in this bundle, just checkout the list below!
```

##### Built-in resource owners:

- [37signals](resource_owners/37signals.md)
- [Asana](resource_owners/asana.md)
- [Amazon](resource_owners/amazon.md)
- [Auth0](resource_owners/auth0.md)
- [Azure](resource_owners/azure.md)
- [Bitbucket](resource_owners/bitbucket.md)
- [Bitly](resource_owners/bitly.md)
- [BufferApp](resource_owners/bufferapp.md)
- [Clever](resource_owners/clever.md)
- [DeviantArt](resource_owners/deviantart.md)
- [Discogs](resource_owners/discogs.md)
- [Disqus](resource_owners/disqus.md)
- [Dropbox](resource_owners/dropbox.md)
- [EVE Online](resource_owners/eve_online.md)
- [Eventbrite](resource_owners/eventbrite.md)
- [Facebook](resource_owners/facebook.md)
- [FI-WARE](resource_owners/fiware.md)
- [Flickr](resource_owners/flickr.md)
- [Foursquare](resource_owners/foursquare.md)
- [GitHub](resource_owners/github.md)
- [GitLab](resource_owners/gitlab.md)
- [Google](resource_owners/google.md)
- [Hubic](resource_owners/hubic.md)
- [Instagram](resource_owners/instagram.md)
- [itembase](resource_owners/itembase.md)
- [Jira](resource_owners/jira.md)
- [Linkedin](resource_owners/linkedin.md)
- [Mail.ru](resource_owners/mailru.md)
- [Odnoklassniki](resource_owners/odnoklassniki.md)
- [PayPal](resource_owners/paypal.md)
- [QQ](resource_owners/qq.md)
- [Reddit](resource_owners/reddit.md)
- [Salesforce](resource_owners/salesforce.md)
- [SensioLabs Connect](resource_owners/sensio_connect.md)
- [Sina Weibo](resource_owners/sina_weibo.md)
- [Spotify](resource_owners/spotify.md)
- [Soundcloud](resource_owners/soundcloud.md)
- [Stack Exchange](resource_owners/stack_exchange.md)
- [Stereomood](resource_owners/stereomood.md)
- [Strava](resource_owners/strava.md)
- [Toshl](resource_owners/toshl.md)
- [Trello](resource_owners/trello.md)
- [Twitch](resource_owners/twitch.md)
- [Twitter](resource_owners/twitter.md)
- [Wunderlist](resource_owners/wunderlist.md)
- [Vkontakte](resource_owners/vkontakte.md)
- [Windows Live](resource_owners/windows_live.md)
- [XING](resource_owners/xing.md)
- [Yahoo](resource_owners/yahoo.md)
- [Yandex](resource_owners/yandex.md)
- [Others](resource_owners/others.md)

### CSRF protection

Set the _csrf_ option to **true** in the resource owner's configuration in order to protect your users from [CSRF](https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)) attacks.
```yaml
# app/config/config.yml
hwi_oauth:
    resource_owners:
        any_name:
            type:                resource_owner_of_choice
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                csrf: true
```

### Continue to the next step!
When you're done. Continue by configuring the security layer.


[Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
