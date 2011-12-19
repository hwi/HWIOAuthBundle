<?php

namespace Knp\OAuthBundle\Security\Http\OAuth;

interface OAuthProviderInterface
{
    function getUsername($accessToken);

    function getAuthorizationUrl(array $extraParameters = array());

    function getAccessToken($code, array $extraParameters = array());
}