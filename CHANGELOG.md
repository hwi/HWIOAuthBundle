Changelog
=========

### 2012-08-13
* Added `UserResponseInterface#getRealName()` method, also new default path `realname`
  was added, this path holds real name of user
* Added new path `uuid` that now hold an unique user identifier
* [BC break] Method `UserResponseInterface#getUsername()` now always returns an real
  unique user identifier, an uses path `uuid`
* [BC break] Path `username` no longer holds an unique user identifier
* [BC break] `OAuth1RequestTokenStorageInterface#save()` second param `$token` now
  must be an array

### 2012-07-15

* Added `UserResponseInterface#getAccessToken()` and `UserResponseInterface#setAccessToken`
* `OAuthToken#getCredentials()` returns an empty string to be consistent with
  the security component. The access token can still be retrieved from the
  `getAccessToken()` method

### 2012-07-06

* All authentication requests are now redirected to the login path

### 2012-07-03

* `firewall_name` is a required setting

### 2012-06-28

* OAuth 1.0a support (linkedin/twitter/generic)
* [BC break] Configuration type 'generic' is renamed to 'oauth2'
* [BC break] `redirect.xml` routing has to be imported. See the setup docs
