RageOAuthBundle
==============

[![Build Status](https://secure.travis-ci.org/ragephp/RageOAuthBundle.svg?branch=master)](http://travis-ci.org/hwi/HWIOAuthBundle)

The RageOAuthBundle adds support for authenticating users via OAuth1.0a or OAuth2 in Symfony2.

Not invented here
-------

This is slightly modified version of HWIOAuthBundle. Please, don't use it if you don't fully understand what you're doing.
We modified this bundle to allow less painful usage of this bundle in our projects.

Differences between RageOAuthBundle and HWIOAuthBundle
-------

- Removed FOSUserBundle support
- Removed PHP <5.6 and Symfony <2.7 versions support
- Improved exception handling in OAuthListener
- Switched to RageOAuthProvider, the modified version OAuthProvider which makes less queries to remote APIs and exception-proof.

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
