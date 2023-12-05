Step 2x: Setup Telegram
====================
First you will need to create bot via [BotFather](https://telegram.me/BotFather) and then set you site domain to it

```
/newbot
nameof_bot
```

Save token somewhere to put it as `client_secret` in config file

```
/setdomain
example.com
```
Or you can do it via botfather buttons.

Next configure a resource owner of type `telegram` with appropriate `client_secret`

```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:          telegram
            client_id:     NOT_REQUIRED # but required by bundle
            client_secret: <bot_token>
```

When you're done, continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
