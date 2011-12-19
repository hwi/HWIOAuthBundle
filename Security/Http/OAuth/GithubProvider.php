<?php

namespace Knp\OAuthBundle\Security\Http\OAuth;

use Knp\OAuthBundle\Security\Http\OAuth\OAuthProvider;

class GithubProvider extends OAuthProvider
{
    protected $options = array(
        'authorization_url' => 'https://github.com/login/oauth/authorize',
        'access_token_url'  => 'https://github.com/login/oauth/access_token',
        'infos_url'         => 'https://github.com/api/v2/json/user/show',
        'username_path'     => 'user.login',
    );
}