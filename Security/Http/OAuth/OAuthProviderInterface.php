<?php

namespace Knp\OAuthBundle\Security\Http\OAuth;

interface OAuthProviderInterface
{
    function getUsername($accessToken);

    function getAuthorizationUrl(array $extraParameters = array());

    function getAccessTokenUrl($code, array $extraParameters = array());
}