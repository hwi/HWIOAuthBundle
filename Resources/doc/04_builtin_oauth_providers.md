# KnpOAuthBundle

## Built-in OAuth Providers

### oauth

This is the most basic provider. It requires all of the available options to work, except `infos_url` and `username_path`. Although it will work without these options, I would not recommend such a setup since it's going to try to load user's using the access token as the username, which is a bit silly to say the least.

### github

A provider pre-configured for [Github](http://github.com/). The only required options are `client_id` and `secret`.

Defaults for the other options:

    authorize_url:    https://github.com/login/oauth/authorize
    access_token_url: https://github.com/login/oauth/access_token
    infos_url:        https://github.com/api/v2/json/user/show
    username_path:    user.login
    scope:            ~

You can override any of these options.

Don't see what you need there? Try a [custom provider](05_custom_oauth_providers.md).