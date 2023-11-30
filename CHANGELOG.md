Changelog
=========
## 2.1.0 (2023-11-30)
* BC Break: Dropped support for Symfony: `>6.0, <6.3`,
* Added: New Passage resource owner,
* Bugfix: Remove deprecations reported by Symfony 6.4,
* Chore: Added support for Symfony 7,

## 2.0.0 (2023-10-01)
* Bugfix: Prevent refreshing non-expired tokens
* Bugfix: Remove deprecations reported by Symfony 6.x
* Bugfix: Prevent fatal error when token doesn't have resource owner name set

## 2.0.0-BETA3 (2023-08-20)
* BC Break: Dropped support for Symfony: 6.0.*,
* BC Break: Class `Templating\Helper\OAuthHelper` was merged into `Twig\Extension\OAuthRuntime`,
* BC Break: When resource owner class doesn't define `TYPE` constant or is `null`, then key will be calculated by converting its class name without `ResourceOwner` suffix to `snake_case`, if neither is felt, then `\LogicException` will be thrown,
* Deprecated: method `UserResponseInterface::getUsername()` was deprecated in favour of `UserResponseInterface::getUserIdentifier()` to match changes in Symfony Security component,
* Enhancement: `@internal` resourceOwner oauth types in Configuration are calculated automatically by scandir. All classes extended from `GenericOAuth[X]ResourceOwner` get `oauth[X]` type. If class only implements ResourceOwnerInterface then its oauth type is `unknown`. ResourceOwner key (parameter `type` in configs) should have defined ResourceOwner::TYPE constant. Each user defined custom ResourceOwner class that implemented `ResourceOwnerInterface` will be registered automatically. If `autoconfigure` option is disabled user have to add the tag `hwi_oauth.resource_owner` to the service definition,
* Enhancement: Class `ConnectController` was split into two smaller ones, `Connect\ConnectController` & `Connect\RegisterController`,
* Bugfix: Added `OAuth1ResourceOwner` & `OAuth2ResourceOwner` to cover case of implementing custom oauth resource owners,
* Bugfix: Fixed Authorization Header in `CleverResourceOwner::doGetRequest`,
* Bugfix: Catch also the `TransportExceptionInterface` in `AbstractResourceOwner::getResponseContent()` method,
* Bugfix: Current matched Firewall is respected during generation of resource owner check path links,
* Bugfix: Prevent fatal error in `OAuthUserProvider::loadUserByOAuthUserResponse()` when `nickname` is not available in OAuth response,
* Bugfix: Use newer version of `firebase/php-jwt` library,
* Chore: Removed not used Symfony Templating component

## 2.0.0-BETA2 (2022-01-16)
* Deprecated: configuration parameter `firewall_names`, firewalls are now computed automatically - all firewalls that have defined `oauth` authenticator/provider will be collected,
* Added: Ability to automatically refresh expired access tokens (only for derived from `GenericOAuth2ResourceOwner` resource owners), if option `refresh_on_expire` set to `true`,
* Enhancement: Refresh token listener is disabled by default and will only be enabled if at least one resource owner has option `refresh_on_expure` set to `true`,
* Enhancement: (`@internal`) Removed/replaced redundant argument `$firewallNames` from controllers. If controller class was copied and replaced, adapt list of arguments: In controller use `$resourceOwnerMapLocator->getFirewallNames()`,
* Bugfix: `RefreshTokenListener` cannot be lazy. If current firewall is lazy (or anonymous: lazy) then current auth token is often initializing on `kernel.response`. In this case new access token will not be stored in session. Therefore, the expired token will be refreshed on each request,
* Bugfix: `InteractiveLoginEvent` will be triggered also for `OAuthAuthenticator`,
* Maintain: Changed config files from `*.xml` to `*.php` (services and routes). Xml routing configs `connect.xml`, `login.xml` and `redirect.xml` are steel present but deprecated. Please use `*.php` variants in your includes instead.

## 2.0.0-BETA1 (2021-12-10)
* BC Break: Dropped PHP 7.3 support,
* BC Break: Dropped support for Symfony: >=5.1 & <5.4,
* BC Break: `OAuthExtension` is now a lazy Twig extension using a Runtime,
* BC Break: removed support for `FOSUserBundle`,
* BC Break: changed `process()` argument for `Form/RegistrationFormHandlerInterface`, from `Form $form` to `FormInterface $form`,
* BC Break: changed form class name in template `Resources/views/Connect/connect_confirm.html.twig` from `fos_user_registration_register` to `registration_register`,
* BC Break: removed configuration option `fosub` from `oauth_user_provider`,
* BC Break: removed configuration options `hwi_oauth.fosub`, & all related DI parameters,
* BC Break: removed DI parameter `hwi_oauth.registration.form.factory` in favour of declaring form class name as DI parameter: `hwi_oauth.connect.registration_form`,
* BC Break: changed `ResourceOwnerMapInterface::hasResourceOwnerByName` signature, update if you use a custom resource owner,
* BC Break: changed `ResourceOwnerMapInterface::getResourceOwnerByName` signature, update if you use a custom resource owner,
* BC Break: changed `ResourceOwnerMapInterface::getResourceOwnerByRequest` signature, update if you use a custom resource owner,
* BC Break: changed `ResourceOwnerMapInterface::getResourceOwnerCheckPath` signature, update if you use a custom resource owner,
* BC Break: `ResourceOwnerMap` uses service locator instead of DI container,
* BC Break: Removed abstract services: `hwi_oauth.abstract_resource_owner.generic`, `hwi_oauth.abstract_resource_owner.oauth1` & `hwi_oauth.abstract_resource_owner.oauth2`,
* BC Break: Removed `setName()` method from `OAuth/ResourceOwnerInterface`,
* BC Break: changed `__construct()` argument for `OAuth/ResourceOwner/AbstractResourceOwner`, from `HttpMethodsClient $httpClient` to `HttpClientInterface $httpClient`,
* BC Break: replaced `php-http/httplug-bundle` with `symfony/http-client`
* BC Break: removed `hwi_oauth.http` configuration,
* BC Break: reworked bundles structure to match Symfony best practices:
  - bundle code moved to: `src/`,
  - tests moved to: `tests/`,
  - docs moved from `Resources/doc` into: `docs/`,
* BC Break: routes provided by bundle now have `methods` requirements:
  - `hwi_oauth_connect_service`: `GET` & `POST`,
  - `hwi_oauth_connect_registration`: `GET` & `POST`,
  - `hwi_oauth_connect`: `GET`,
  - `hwi_oauth_service_redirect`: `GET`,
* Added support for PHP 8.1,
* Added support for Symfony 5.6,

## 1.4.5 (2021-12-08)
* Bugfix: Fixed: BC break by restoring wrongly moved `AbstractOAuthToken::getCredentials()` method,

## 1.4.3 (2021-12-07)
* Bugfix: Fixed support for PHP 8.1,
* Bugfix: Fixed support for Symfony 5.4, 
* Bugfix: Fixed `VkontakteResourceOwner` option: `api_version` to not point to deprecated one,
* Bugfix: `RequestStack::getMasterRequest()` is deprecated since Symfony 5.3, use `RequestStack::getMainRequest()` if exists,
* Maintain: Added `GenericOAuth1ResourceOwnerTestCase`, `GenericOAuth2ResourceOwnerTestCase` & `ResourceOwnerTestCase` test case classes for easier unit testing custom resource owners

## 1.4.2 (2021-08-09)
* Bugfix: remove `@final` declaration from `OAuthFactory` & `FOSUBUserProvider`,
* Maintain: added `.gitattributes` to reduce amount of code in archives,

## 1.4.1 (2021-07-28)
* Bugfix: Define missing `hwi_oauth.connect.confirmation` parameter,
* Bugfix: Added missing success/failure handlers,

## 1.4.0 (2021-07-26)
* BC Break: dropped Symfony 5.0 support as it is EOL,
* BC Break: dropped PHP 7.2 support as it is EOL,
* BC Break: changed `__construct()` argument for `OAuth/RequestDataStorage/SessionStorage`, from `SessionInterface $session` to `RequestStack $requestStack`,
* BC Break: all internal classes are "softly" marked as `final`,
* Added: Symfony 5.1 Security system support,
* Added: Forward compatibility layer for session service deprecation,
* Added: state support for service authentication URL's,
* Added: ability to change the response after `HWIOAuthEvents::CONNECT_COMPLETED` is fired,
* Added: PHPStan static analyse into CI,
* Fixed: `OAuthProvide` to properly refresh data inside tokens,
* Fixed: PHP notice in `AppleResourceOwner`,
* Fixed: use new GitHub API in `GitHubResourceOwner`,
* Fixed: functional tests with & without FOSUserBundle,
* Fixed: controller don't depend on service container if possible,
* Maintain: removed `Wunderlist` resource owner,
* Maintain: removed several Symfony BC layers,
* Maintain: removed Prophecy in favour of PHPUnit mocking,

## 1.3.0 (2021-01-03)
* BC Break: dropped support for Symfony `<4.4`,
* BC Break: dropped support for Doctrine Bundle `<2.0`,
* Added PHP 8 support,
* Upgraded Facebook API to v8.0,
* Upgraded Twitch resource owner to incorporate latest Twitch API,
* Fixed: undefined `id_token` exception in Azure resource owner,
* Docs: changed firewall name to match flex receipt,
* Maintain: moved from Travis CI to Github Actions,

## 1.2.0 (2020-10-19)
* BC Break: dropped Symfony 4.3 support,
* Added `first_name` & `last_name` in AzureResourceOwner,
* Added: support for multiple OAuth2 state parameters,
* Added: Apple resource owner,
* Fixed: updated Azure `authorization` & `access_token` urls,
* Fixed: Doctrine persistence deprecation errors,
* Allow modification of the response in `FilterUserResponseEvent`,

## 1.1.0 (2020-04-06)
* Added Symfony 5 support,
* Added domain whitelist service to avoid open redirect on `target_path`,
* Fixed: session service was not injected in `LoginController`,
* Fixed: missing `setContainer` call to service configuration for `LoginController`,
* Fixed: client id and client secret must be set in `Auth0ResourceOwner::doGetTokenRequest`,
* Fixed: missing client id and client secret in `Auth0ResourceOwner`,
* Twig dependency on `LoginController` is now optional,

## 1.0.0 (2020-01-17)
* Dropped support for PHP 5.6, 7.0 and 7.1,
* Dropped support for FOSUserBundle 1.3,
* Dropped support for Symfony 2.8,
* Minimum Symfony 3 requirement is 3.4,
* Minimum Symfony 4 requirement is 4.3,
* Fixed: WindowsLive Resource Owner token request,
* Fixed: Update Facebook API to v3.1,
* Fixed: Update Linkedin API to v2,
* Fixed: YahooResourceOwner::doGetUserInformationRequest uses wrong arguments,
* Fixed: Symfony deprecation warning in `symfony/config`,
* Fixed: SensioConnect now uses new API URLs,
* Fixed: Do not add Authorization header if no client_secret is present,
* Fixed: `LoginController::connectAction` should not fail if no token is available,
* Added: Genius.com resource owner,
* Added: HTTPlug 2.0 support,
* Added: Keycloak resource owner,
* Added: The controller is now available as a service,
* Added: Allow to use HTTP Basic auth for token request,
* [BC break] Class `Configuration` has been marked final,
* [BC break] Class `ConnectController` has been marked final,
* [BC break] Class `HWIOAuthExtension` has been marked final,
* [BC break] Class `OAuthExtension` has been marked final,
* [BC break] Class `SetResourceOwnerServiceNameCompilerPass` has been marked final,
* [BC break] Class `ConnectController` extends `AbstractController` instead of `Controller`,
* [BC break] Service `hwi_oauth.http_client` has been marked private,
* [BC break] Service `hwi_oauth.security.oauth_utils` has been marked private,
* [BC break] Several service class parameters have been removed,

## 0.6.3 (2018-07-31)
* Fixed: Vkontakte profile picture & nickname path,
* Fixed: `Content-Length` header must be a string,
* Fixed: Upgraded GitLab end point to v4,
* Fixed: Resource owner map parameters must be public,
* Fixed: Azure resource owner `infos_url` should not be empty,
* Fixed: Don't start sessions twice & don't start sessions if already started,
* Fixed: Updated BitBucket docs,
* Added: Further compatibility changes for Symfony 4.1,
* Added: LinkedIn `first-` & `last-` names,
* Added: Facebook profile picture

## 0.6.2 (2018-03-28)
* Fixed: VK requires API version now,
* Fixed: Updated Slack resource owner to use new Slack API methods,
* Fixed: Changing authorization and access token to v2 for LinkedIn,
* Fixed: Fix double call of `getUserInformation()` in `ConnectController`,
* Fixed: Fix serialization of `AccountNotLinkedException`,
* Fixed: Check for grant_rule value `IS_AUTHENTICATED_FULLY` in DI configuration,
* Fixed: Don't execute `OAuthProvider::refreshAccessToken()` when there is no refresh token

## 0.6.1 (2018-01-23)
* BC BREAK: Replaced `PHPUnit_Framework_TestCase` with `PHPUnit\Framework\TestCase` in tests,
* Added: Implemented `getUserInformation()` for Dropbox v2,
* Fixed: Headers passed to `httpRequest()` method in various resource owners,
* Fixed: Marked some services as `public` to make code compatible with Symfony 4

## 0.6.0 (2017-12-01)
* BC BREAK: Fully replaced Buzz library with usage of HTTPlug & Guzzle 6,
* BC BREAK: `hwi.http_client` config options are remove. HTTP configuration must rely on the HTTPlug client,
* BC BREAK: Template engine other than Twig are no longer supported,
* BC BREAK: Option `hwi_oauth.templating_engine` was removed,
* Added: Symfony 4 support,
* Added: `php-http/httplug-bundle` support, to auto-provide needed HTTPlug services and get full Symfony integration,
* Added: `hwi.http.client` and `hwi.http.message_factory` config keys to provide your own HTTPlug services,
* Added: `HWIOAuthEvents`,
* Added: `ResourceOwnerInterface::addPaths()` method for easier managing paths in resource owners,
* Fixed: Update Facebook API to v2.8,

## 0.5.3 (2017-01-08)
* Fixed: Bitbucket2 resource owner,
* Fixed: GitHub resource owner documentation,
* Fixed: Don't require any form for the connect feature,
* Fixed: Uncaught exception with custom error page,
* Fixed: `php-cs-fixer` updated to latest version & run on base code

## 0.5.2 (2016-12-12)
* Fixed: Prevent uncaught exception when redirecting to invalid route,
* Fixed: Add more details too exception when account was not linked,
* Fixed: Odnoklassinki resource owner,
* Fixed: Office365 resource owner,
* Fixed: StackExchange resource owner,
* Fixed: WeChat resource owner,
* Fixed: WindowsLive resource owner

## 0.5.1 (2016-10-03)
* Fixed error that could occur with message "302 Header already sent",
* Exclude tests from Composer autoloader

## 0.5.0 (2016-09-11)
* Fixed: `OAuthHelper` should fallback to new `Request` in case of receiving `null`,
* Fixed: Better `FOSUserBundle` integration,
* Fixed: Serialization issue in `WechatResourceOwner`,
* Fixed: Incorrect refresh token in `WechatResourceOwner`,
* Fixed: Broken `TrelloResourceOwner`,
* Fixed: Removed dead code in `OAuthProvider`,
* Fixed: Update Facebook API to v2.7,
* Added: Symfony 3 support,
* Added: Redirect to `target_path` after successful registration/connection,
* Added: Asana resource owner,
* Added: Bitbucket resource owner,
* Added: Clever resource owner,
* Added: Itembase resource owner,
* Added: Jawbon resource owner,
* Added: Office365 resource owner,
* Added: Wunderlist resource owner,
* Added: Hungarian translation

## 0.4.3 (2016-09-11)
* Fixed: Request parameters are not copied into new Request on forward,
* Fixed: Fixed scope deprecating message,
* Fixed: Resolved deprecated message in ConnectController,
* Fixed: Removed usage of deprecated code in tests

## 0.4.2 (2016-07-27)
* Fixed: Change Discogs URL from http to https,
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
