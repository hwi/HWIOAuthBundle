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

## Differences since 0.6+ version

On v0.5 and below, you were able to configure your HTTP client like that:

```yaml
# app/config/config.yml

hwi_oauth:
    http_client:
        timeout:       10 # Time in seconds, after library will shutdown request, by default: 5
        verify_peer:   false # Setting allowing you to turn off SSL verification, by default: true
        ignore_errors: false # Setting allowing you to easier debug request errors, by default: true
        max_redirects: 1 # Number of HTTP redirection request after which library will shutdown request,
                         # by default: 5
        proxy: "example.com:8080" # String with proxy configuration for cURL connections, ignored by default.
                                    # "" -> don't set proxy and will use proxy system
                                    # "example.com:8080" -> set custom proxy
                                    # ":" -> disable proxy usage, ignoring also proxy system and ENVIRONMENT variables
```

As we now use Httplug, the http configuration does not rely on our bundle anymore.
Still, you can always configure Guzzle6 (if you use this client) thanks to the
[Guzzle6 adapter](http://docs.php-http.org/en/latest/clients/guzzle6-adapter.html).

### Configure with HttplugBundle

You need to [install and configure](../1-setting_up_the_bundle.md) the HttplugBundle first.

Then apply your configuration trough `httplug` section:

```yaml
# app/config/config.yml

httplug:
    clients:
        default:
            factory: 'httplug.factory.curl'
        hwi_special:
            factory: 'httplug.factory.guzzle6'
            config: # You pass here the Guzzle configuration, exactly like before.
                timeout: 10
                verify_peer: false
                max_redirects: 1
                ignore_errors: false
                proxy: "example.com:8080"

hwi_oauth:
    http:
        client: httplug.client.hwi_special # Then you specify the special service to use.
```

And voilà! You now have your custom Guzzle 6 service with the same configuration.

### Configure manually

If you don't want to use HttplugBundle, you can still configure Guzzle quite easily.

First, you have to declare your Guzzle 6 adapter and message factory as services, with your custom configuration:

```yaml
# app/config/services.yml

services:
    guzzle_client.hwi_special:
        class: Http\Adapter\Guzzle6\Client
        factory:
            - Http\Adapter\Guzzle6\Client
            - createWithConfig
        arguments:
            - timeout: 10
              verify_peer: false
              max_redirects: 1
              ignore_errors: false
              proxy: "example.com:8080"
    guzzle_client.message_factory:
        class: Http\Message\MessageFactory\GuzzleMessageFactory
```

Then configure the bundle to use the service created above:

```yaml
# app/config/config.yml

hwi_oauth:
    http:
        client: guzzle_client.hwi_special
        message_factory: guzzle_client.message_factory
```

That's it. Now your custom Httplug Guzzle adapter service is used.

[Return to the index.](../index.md)
