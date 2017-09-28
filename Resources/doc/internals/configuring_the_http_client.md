Internals: Configuring the HTTP Client
======================================
As you already noticed, HWIOAuthBundle depends on [Httplug](http://httplug.io)
a lightweight library for issuing HTTP requests.

The HTTP client configuration does now directly rely on HttplugBundle if installed.
You can see all configuration options and plugins on the
[vendor documentation](http://docs.php-http.org/en/latest/integrations/symfony-bundle.html#usage).

If you want to use a different Httplug client/factory than the default one(s), you can specify it:

```yaml
# app/config/config.yml

httplug:
    clients:
        default:
            factory: 'httplug.factory.curl'
        hwi_special:
            factory: 'httplug.factory.guzzle6'

hwi_oauth:
    http:
        client: httplug.client.hwi_special # Default to httplug.client.default
        factory: httplug.stream_factory    # Default to httplug.message_factory.default
```

If you don't want to use the HTTPlug Bundle, the client and factory services **must be specified**.

[Return to the index.](../index.md)
