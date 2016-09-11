Changelog
=========
## 0.4.3 (2016-09-11)
* Fixed: Request parameters are not copied into new Request on forward
* Fixed: Fixed scope deprecating message
* Fixed: Resolved deprecated message in ConnectController
* Fixed: Removed usage of deprecated code in tests

## 0.4.2 (2016-07-27)
* Fixed: Change Discogs URL from http to https
* Fixed: Update Facebook API URLs to not use outdated ones

## 0.4.1 (2016-03-08)
* Fixed: Remove usage of deprecated Twig function `form_enctype` & replace with usage of `form_start`/`form_end`,
* Fixed: Mark as not fully compatible with Symfony `~3.0`,
* Fixed: Multiple firewalls can now have different resource owners,
* Fixed: Wrong URL generated for Safesforce resource owner,
* Added: `include_email` option into Twitter resource owner,
* Added: Hungarian translation,
* Added: Documentation about FOSUser integration

## 0.4.0 (2015-12-04)
* [BC break] Added `UserResponseInterface#getFirstName()` method, also a new default path `firstname`
  was added, this path holds the first name of user,
* [BC break] Added `UserResponseInterface#getLastName()` method, also a new default path `lastname`
  was added, this path holds the last name of user,
* [BC break] Added `UserResponseInterface::getOAuthToken()` & basic implementation in `AbstractUserResponse`,
* [BC break] `GenericOAuth1ResourceOwner::getRequestToken()` is now public method (was protected),
* Added: configuration parameter `firewall_name` (will be removed in next major version)
  renamed to `firewall_names` to support multiple firewalls,
* Added: configuration parameter: `failed_auth_path` which contains route name, on which user
  will be redirected after failure when connecting accounts (i.e. user denies connection),
* Added: `appsecret_proof` functionality support to the Facebook resource owner,
* Added: `sandbox` functionality support to the Salesforce resource owner,
* Added Auth0 resource owner,
* Added Azure resource owner,
* Added BufferApp resource owner,
* Added Deezer resource owner,
* Added Discogs resource owner,
* Added EveOnline resource owner,
* Added Fiware resource owner,
* Added Hubic resource owner,
* Added Paypal resource owner,
* Added Reddit resource owner,
* Added Runkeeper resource owner,
* Added Slack resource owner,
* Added Spotify resource owner,
* Added Soundcloud resource owner,
* Added Strava resource owner,
* Added Toshl resource owner,
* Added Trakt resource owner,
* Added Wechat resource owner,
* Added Wordpress resource owner,
* Added Xing resource owner,
* Added Youtube resource owner,
* Fixed: Revoking tokens for Facebook & Google resource owners,
* Fixed: Instagram allows only GET calls to fetch user details,
* Fixed: `ResourceOwnerMap` no longer depends on deprecated `ContainerAware` class,
* Fixed: Wrong usage of `json_decode` in Mail.ru resource owner,
* Fixed: Transform storage exceptions in OAuth1 resource owners into `AuthenticationException`
* Fixed: Default scopes & fields for VKontakte resource owner

## 0.3.9 (2015-08-28)
* Fix: Remove deprecated Twig features
* Fix: Undefined variable in `FOSUBUserProvider::refreshUser`
* Fix: Restore property accessor for Symfony 2.3

## 0.3.8 (2015-05-04)
* Fix: Remove BC break for Symfony < 2.5,
* Fix: Compatibility issues with Symfony 2.6+,
* Fix: Deprecated graph URLs for `FacebookResourceOwner`

## 0.3.7 (2014-11-15)
* Fix: `SessionStorage::save()` could throw php error,
* Fix: `OAuthToken::isExpired()` always returned `false`,
* Fix: `FoursquareResourceOwner`, `TwitchResourceOwner`, `SensioConnectResourceOwner`
  not working with bearer header,
* Fix: Don't use deprecated fields in `FacebookResourceOwner`,
* Fix: `FOSUBUserProvider::refreshUser()` always returning old user,

## 0.3.6 (2014-06-02)
* Fix: `InstagramResourceOwner` regression while getting user details,
* Fix: Add smooth migration for session (de)serialization

## 0.3.5 (2014-05-30)
* Fix: `LinkedinResourceOwner` regression while getting user details,
* Fix: OAuth `revoke` functionality to be available wider,
* Fix: Removed undocumented functionality from `SinaWeiboResourceOwner`,
* Fix: Always remove default ports from URLs to match OAuth 1.0a, Spec: 9.1.2

## 0.3.4 (2014-05-12)
* Fix: Instagram OAuth redirect to one url,
* Fix: `FOSUBUserProvider` should also implement `UserProviderInterface`,
* Fix: `YahooResourceOwner` `infos_url` to use new format,
* Fix: Send authorization via headers instead of URL parameter,
* Fix: `GithubResourceOwner` revoke method,
* Fix: Add login routing documentation note

## 0.3.3 (2014-02-17)
* Fix: Incorrect redirect URL when no parameters are set,
* Fix: Add missing parameter `prompt` for `GoogleResourceOwner`,
* Fix: `WordpressResourceOwner` user details API call,
* Fix: PHP Notice when `oauth_callback_confirmed` was set too `false`,
* Fix: PHP Fatal when session returns boolean instead of object,
* Fix: Add missing query parameters for `FacebookResourceOwner`

## 0.3.2 (2014-02-07)
* Fix: Prevent `SessionUnavailableException` when returns back from service,
* Fix: `EntityUserProvider` should implement `UserProviderInterface`,
* Fix: `createdAt` property was missing when serializing the `OAuthToken`,
* Added Italian translations

## 0.3.1 (2014-01-17)
* Fix: Change Twitter API call to use SSL URL,
* Fix: Problems with options in `VkontakteResourceOwner`,
* Fix: Problems with OAuth 1.0a token & `YahooResourceOwner`,
* Fix: Throw exception in `FOSUBUserProvider` when username is missing
* Added SalesForce resource owner

## 0.3.0 (2013-09-28)
* [BC break] `AccountConnectorInterface::connect()` method now requires the first
  parameter to be instance of `Symfony\Component\Security\Core\User\UserInterface`
* [BC break] `ConnectController::authenticateUser()` method now requires the first
  parameter to be instance of `Symfony\Component\HttpFoundation\Request`
* [BC break] Removed `AbstractResourceOwner::addOptions()` method
* [BC break] `OAuthUtils::getAuthorizationUrl()` & `OAuthUtils::getLoginUrl()` methods
  now expect first parameter to be instance of `Symfony\Component\HttpFoundation\Request`
* [BC break] LinkedIn resource owner now uses OAuth2 approach, visit official
  web page for details how to migrate: https://developer.linkedin.com/documents/authentication#migration
* [BC break] Dropbox resource owner now uses OAuth2 approach
* Added ability to merge response parts into single path
* Added Bitly resource owner
* Added Box resource owner
* Added Dailymotion resource owner
* Added DeviantArt resource owner
* Added Eventbrite resource owner
* Added Mail.ru resource owner
* Added Sina Weibo resource owner
* Added QQ.com resource owner
* Added Trello resource owner
* Added Wordpress resource owner

## 0.3.0-alpha2 (2013-07-29)
* [BC break] Added `ResourceOwnerInterface::isCsrfTokenValid()` method
* [BC break] Removed `OAuth1RequestTokenStorageInterface` along with the implementations
* [BC break] `AbstractResourceOwner::__construct()` now requires `RequestDataStorageInterface`
  instance as last argument
* Fix: Yandex resource owner using invalid parameter when requesting user data
* Fix: To prevent unusual content headers response from resource owners should
  be first threaten as json and only in case of failure threaten as query text
* Fix: Instagram resource owner is not able to receive user data more than once
* Added ability to disable confirmation page when connecting accounts
* Added CSRF protection for OAuth2 providers (turned off by default)
* Added `RequestDataStorageInterface` along with implementation
* Added Stereomood resource owner

## 0.3.0-alpha1 (2013-07-03)
* [BC break] `GenericOAuth2ResourceOwner::getAccessToken()` now returns an array
  instead of a string. This array contains the access token and its 'expires_in'
  value, along with any other parameters returned from the authentication provider
* [BC break] Added `OAuthAwareExceptionInterface#setToken()`, `OAuthAwareExceptionInterface#getRefreshToken()`,
  `OAuthAwareExceptionInterface#getRawToken()`, `OAuthAwareExceptionInterface#getExpiresIn()`
  methods
* [BC break] Renamed `AbstractResourceOwner::doGetAccessTokenRequest` to `doGetTokenRequest`
* [BC break] Removed `AdvancedPathUserResponse` & `AdvancedUserResponseInterface`
* [BC break] Added `UserResponseInterface#getEmail()`, `UserResponseInterface#getProfilePicture()`,
  `UserResponseInterface#getRefreshToken()`, `UserResponseInterface#getExpiresIn()`,
  `UserResponseInterface#setOAuthToken()` methods
* [BC break] Removed `UserResponseInterface::setAccessToken()` method
* [BC break] Removed `AbstractUserResponse::getOAuthToken()` method because it was ambiguous
* [BC break] `PathUserResponse#setPaths()` method no longer overwrite default paths
* [BC break] `PathUserResponse#getPath()` method no longer throws an exception if path
  not exists
* [BC break] `PathUserResponse#getValueForPath()` removed second argument from this method,
  it will not throw exception anymore if response or value is missing, but now will return
  `null` instead
* [BC break] Added `ResourceOwnerInterface#getOption($name)` method
* [BC break] `ResourceOwnerInterface#getUserInformation()` now must receive array (`$accessToken`)
  as first parameter, also added second parameter (`$extraParameters`) to be consistent
  along all implementations
* Added `OAuthToken::getRefreshToken()`, `OAuthToken::setRefreshToken()`, `OAuthToken::getExpiresIn()`,
  `OAuthToken::setExpiresIn()`, `OAuthToken::getRawToken()`, `OAuthToken::setRawToken()`
* Added `AbstractResourceOwner#addOptions()` & `ResourceOwnerInterface#setOption($name, $value)`
  methods which allows easy overwriting resource specific options
* Added support for options: `access_type`, `request_visible_actions`, `approval_prompt` & `hd`
  in Google resource owner
* Added 37signals resource owner
* Added Amazon resource owner
* Added Bitbucket resource owner
* Added Disqus resource owner
* Added Dropbox resource owner
* Added Flickr resource owner
* Added Instagram resource owner
* Added Odnoklassniki resource owner
* Added Yandex resource owner

## 0.2.10 (2013-12-09)
* Fix: use `Symfony\Component\Security\Core\User\UserInterface` in `EntityUserProvider::refreshUser`
* Fix: made `SessionStorage` compatible with Symfony 2.0

## 0.2.9 (2013-09-25)
* Fix: Regression done in version `0.2.8` blocking usage without `FOSUserBundle`
* Fix: `OAuthUtils::getAuthorizationUrl()` ignoring given redirect URL

## 0.2.8 (2013-09-19)
* Fix: Added missing parts in user providers like: `loadUserByUsername()`
  or `refreshUser()` methods
* Fix: Registering of user provider services
* Fix: Make `OAuthUtils::signRequest()` compatible with OAuth1.0a specification

## 0.2.7 (2013-08-03)
* Fix: Polish oauth error detection to cover cases from i.e. Facebook resource owner
* Fix: Changed authorization url for Vkontakte resource owner

## 0.2.6 (2013-06-24)
* Fix: Use same check for FOSUserBundle compatibility to prevent strange errors
  with calls of undefined services
* Fix: User-land aliased (resource owner) services have the appropriate name

## 0.2.5 (2013-05-29)
* Fix: Use user identifier represented as string for Twitter to prevent issues with
  losing accuracy for large numbers (i.e. Javascript) or type comparison (i.e. MongoDB)
* Fix: Don't depend on `arg_separator.output` data for URL generation to prevent issues

## 0.2.4 (2013-05-15)
* Fix: Throw `Symfony\Component\Security\Core\Exception\AccessDeniedException`
  & `Symfony\Component\HttpKernel\Exception\NotFoundHttpException` instead of `\Exception`
  to make cases more clear
* Fix: Detect `oauth_problem` as authorization error and inform user instead logging error
  in background
* Fix: Request extra parameters should have higher priority than default
* Fix: How urls are build in resource owners
* Fix: Missing parameter in `YahooResourceOwner`

## 0.2.3 (2013-05-06)
* Added `AbstractUserResponse::getOAuthToken()` method to allow fetching only OAuth token details
* Added french translation
* Fix: FB incompatibility with 'error' field in response

## 0.2.2 (2013-04-15)
* Fix: FOSUB registration form handler
* Fix: Use API 1.1 for Twitter, not the deprecated 1.0

## 0.2.1 (2013-03-27)
* Fixed issue with FOSUserBundle 2.x integration

## 0.2.0 (2013-03-26)
* Added support for a `target_path_parameter` in order to control the redirect path after login
* Added `hwi_oauth_authorization_url()` twig helper function
* Added Jira resource owner
* Added Yahoo resource owner
* Added setting `realm` in configuration
* Added support for FOSUserBundle 2.x integration
* Added Stack Exchange resource owner
* Fix: configuration parameter `firewall_name` is required
* Fix: prevent throwing `AlreadyBoundException` when using FOSUserBundle 1.x integration
* Fix: check for availability of `profilePicture` in views before calling it
* Fix: `InMemoryProvider` now shows user nickname as name instead of unique identifier
* Fix: don't set `realm` option if is empty in request headers
* Fix: for infinity loop blockade and error token response handling

## 0.1-alpha (2012-08-27)
* [BC break] Renamed path `username` to `identifier` to make it more clear that this path should
  hold the unique user identifier (previously `username`)
* [BC break] Method `UserResponseInterface#getUsername()` now always returns a real
  unique user identifier, and uses path `identifier`
* [BC break] `OAuth1RequestTokenStorageInterface#save()` second param `$token` must
  now be an array
* [BC break] Configuration type 'generic' is renamed to 'oauth2'
* [BC break] `redirect.xml` routing has to be imported. See the setup docs
* Added `UserResponseInterface#getRealName()` method, also a new default path `realname`
  was added, this path holds the real name of user
* Added `UserResponseInterface#getNickName()` method, also a new default path `nickname`
  was added, this path holds the nickname of user
* Added `UserResponseInterface#getAccessToken()` and `UserResponseInterface#setAccessToken`
* Added `OAuthToken#getCredentials()` returns an empty string to be consistent with
  the security component. The access token can still be retrieved from the
  `getAccessToken()` method
* Added change that forces all authentication requests are now redirected to the login path
* Added change that makes `firewall_name` option required setting
* Added OAuth 1.0a support (linkedin/twitter/generic)
