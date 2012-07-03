Step 2: Configuring resource owners
===================================
HWIOAuthBundle creates a dedicated service for each resource owner you want to
use in your application. These resource owners will be used in the oauth
firewall. The bundle ships several pre-configured resource owners that only
need little configuration.

To make this bundle working you need to add this setting:

``` yaml
# app/config/config.yml

hwi_oauth:
    # name of the firewall in which this bundle is active, this setting MUST be set
    firewall_name: secured_area

    # here you will add one (or more) of configurations for resource owners
    # and other setting you want to adjust in this bundle, just checkout list below!
```

- [Facebook](2x-facebook.md)
- [Google](2x-google.md)
- [GitHub](2x-github.md)
- [Sensio Connect](2x-sensio_connect.md)
- [Windows Live](2x-windows_live.md)
- [Linkedin](2x-linkedin.md)
- [Others](2x-others.md)

### Continue to the next step!
When you're done. Continue by configuring the security layer.
[Step 3: Configuring the security layer](3-configuring_the_security_layer.md).
