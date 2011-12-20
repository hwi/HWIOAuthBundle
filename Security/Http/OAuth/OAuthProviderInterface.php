<?php

namespace Knp\Bundle\OAuthBundle\Security\Http\OAuth;

interface OAuthProviderInterface
{
    function getUsername($accessToken);

    function getAuthorizationUrl($loginCheckUrl, array $extraParameters = array());

    function getAccessToken($code, array $extraParameters = array());
}