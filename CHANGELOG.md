Changelog
=========

### 2012-07-15

* Added `UserResponseInterface#getAccessToken()` and `UserResponseInterface#setAccessToken`
* `OAuthToken#getCredentials()` returns an empty string to be consistent with
  the security component. The access token can still be retrieved from the
  `getAccessToken()` method

### 2012-07-06

* All authentication requests are now redirected to the login path

### 2012-07-03

* `firewall_name` is a required setting
* [BC break] `redirect.xml` and `login.xml` no longer require to be prefixed when imported

### 2012-06-28

* OAuth 1 resource owners: 'linkedin', 'twitter', 'oauth1'
* OAuth 1.0a support
* [BC break] Configuration type 'generic' was renamed to 'oauth2'
* [BC break] `redirect.xml` routing has to be imported. See the setup docs
