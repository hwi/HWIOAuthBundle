# KnpOAuthBundle

## Built-in User Providers

### OAuthUserProvider

This one does nothing but create valid users with default roles (`ROLE_USER` for now) and using `infos_url` in conjunction with `username_path` to provide `getUsername()`'s result. This `UserProvider` is used to represent *remote* OAuth user, when you don't need to do fancy things with your users, such as managing roles and ACLs. Example usage:

    providers:
        secured_area:
            id: knp_oauth.user.provider

### EntityUserProvider

This provider fetches users from the database and creates them on the fly if they don't already exist. It requires Doctrine to work. It works exactly like Doctrine's entity user provider, except its configuration key is `oauth_entity`:

    providers:
        secured_area:
            oauth_entity:
                class: KnpBundlesBundle:User
                property: name

Still have some questions? There might be something for you in the [cookbooks](07_cookbooks.md).