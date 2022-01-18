Step 2x: Setup Apple
=====================
First you will have to register your application on Apple Developers website. Check out the
documentation for more information: https://help.apple.com/developer-account/?lang=en#/devde676e696

Next configure a resource owner of type `apple` with appropriate
`client_id`, `client_secret` and `scope`.
Example `scope` values include:
* `name`
* `email`
```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                apple
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "name email"
```

Apple doesn't provide a ready-to-use `client_secret`, it has to be generated manually using a private key downloadable on Apple Developer.
The generated `client_secret` has an expiration date, so it has to be regenerated continually.
[See Documentation](https://developer.apple.com/documentation/sign_in_with_apple/generate_and_validate_tokens)

To overcome this inconvenience, you can configure an automatic `client_secret` generation as following.
This requires [PHP-JWT](https://github.com/firebase/php-jwt) to work. (`composer require firebase/php-jwt`)
```yaml
# config/packages/hwi_oauth.yaml

hwi_oauth:
    resource_owners:
        any_name:
            type:                apple
            client_id:           <client_id>
            client_secret:       auto
            scope:               "name email"
            options:
                auth_key:        <auth_key>
                key_id:          <key_id>
                team_id:         <team_id>
```

_The auth key can be loaded using an environment variable processor:`%env(file:resolve:APPLE_AUTH_KEY_PATH)%` with `APPLE_AUTH_KEY_PATH=%kernel.project_dir%/path/to/AuthKey_XXXXXXXXXX.p8` set to your `.env`._

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
