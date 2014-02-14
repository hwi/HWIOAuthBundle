Step 2x: Setup Google
=====================
First you will have to register your application on Google. Check out the
documentation for more information: https://developers.google.com/accounts/docs/OAuth2.

Next configure a resource owner of type `google` with appropriate
`client_id`, `client_secret` and `scope`.

Example `scope` values include:
* `email`
* `profile`

They are to be space delimited. Refer to the [Google documentation](https://developers.google.com/accounts/docs/OAuth2Login#scope-param) for more information.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "email profile"
```

If you want to use [offline access](https://developers.google.com/accounts/docs/OAuth2WebServer#offline) you need to add option
`access_type: offline` to your configuration.

```yaml
# app/config/config.yml

hwi_oauth:
    resource_owners:
        any_name:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            options:
                access_type:     offline
```

In case you want to [insert moments](https://developers.google.com/+/api/latest/moments/insert) you will need [`request_visible_actions`](https://developers.google.com/+/web/app-activities/#writing_an_app_activity_using_the_google_apis_client_libraries)
for each activity you're planning to use.
As an example consider following:
```yaml
# app/config/config.yml
hwi_oauth:
    resource_owners:
        any_name:
            type:                google
            client_id:           <client_id>
            client_secret:       <client_secret>
            scope:               "https://www.googleapis.com/auth/plus.login"
            options:
                request_visible_actions: "http://schemas.google.com/AddActivity http://schemas.google.com/CommentActivity"
```

In option `request_visible_actions` there are listed activity types that will be used while inserting as type for Google Moments API.
Please also note that you need to add additional scope `https://www.googleapis.com/auth/plus.login`, to be able use the that API.

When you're done. Continue by configuring the security layer or go back to
setup more resource owners.

- [Step 2: Configuring resource owners (Facebook, GitHub, Google, Windows Live and others](../2-configuring_resource_owners.md)
- [Step 3: Configuring the security layer](../3-configuring_the_security_layer.md).
