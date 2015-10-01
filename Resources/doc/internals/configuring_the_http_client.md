Internals: Configuring the HTTP Client
======================================
As you already noticed, HWIOAuthBundle depends on [Buzz](https://github.com/kriswallsmith/Buzz)
a lightweight library for issuing HTTP requests. This library is used by every of the resource
owners, it's pre-configured by default, but you can adjust some settings of this library to
reflect requirements of your environment.

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

[Return to the index.](../index.md)
