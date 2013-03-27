Changelog
=========

### 2013-05-07
* [BC break] Removed `AdvancedPathUserResponse` & `AdvancedUserResponseInterface`
* [BC break] Added `UserResponseInterface#getEmail()` & `UserResponseInterface#getProfilePicture()`
  methods
* [BC break] `PathUserResponse#setPaths()` method no longer overwrite default paths
* [BC break] `PathUserResponse#getPath()` method no longer throws an exception if path
  not exists
* [BC break] `PathUserResponse#getValueForPath()` removed second argument from this method,
  it will not throw exception anymore if response or value is missing, but now will return
  `null` instead
* [BC break] Added `ResourceOwnerInterface#getOption($name)` method
* Added `AbstractResourceOwner#addOptions()` method which allows easy overwriting resource
  specific options

### 2013-05-06
* Added `AbstractUserResponse::getOAuthToken()` method to allow fetching only OAuth token details

### 2013-03-26
* Added support for a `target_path_parameter` in order to control the redirect path after login

### 2012-08-27
* Added `UserResponseInterface#getRealName()` method, also a new default path `realname`
  was added, this path holds the real name of user
* Added `UserResponseInterface#getNickName()` method, also a new default path `nickname`
  was added, this path holds the nickname of user
* [BC break] Renamed path `username` to `identifier` to make it more clear that this path should
  hold the unique user identifier (previously `username`)
* [BC break] Method `UserResponseInterface#getUsername()` now always returns a real
  unique user identifier, and uses path `identifier`
* [BC break] `OAuth1RequestTokenStorageInterface#save()` second param `$token` must
  now be an array

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
