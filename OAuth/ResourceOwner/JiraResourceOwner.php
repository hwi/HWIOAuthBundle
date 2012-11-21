<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

class JiraResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $options = array(
        'base_url'            => '',
        'authorization_url'   => '{base_url}/plugins/servlet/oauth/authorize',
        'request_token_url'   => '{base_url}/plugins/servlet/oauth/request-token',
        'access_token_url'    => '{base_url}/plugins/servlet/oauth/access-token',
        'infos_url'           => '{base_url}/rest/api/2/user',
        'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm'               => '',
        'signature_method'    => 'RSA-SHA1',
    );

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'name',
        'nickname'   => 'name',
        'realname'   => 'displayName',
        'email'      => 'emailAddress',
    );

    /**
     * {@inheritDoc}
     */
    public function getOption($name)
    {
        $value = parent::getOption($name);

        if ($name !== 'base_url') {
            $value = str_replace('{base_url}', $this->getOption('base_url'), $value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        $sessionUrl = $this->getOption('base_url') . '/rest/auth/1/session';
        $sessionResponse = $this->httpRequest($sessionUrl, null, $parameters, array(), 'GET');

        $data = json_decode($sessionResponse->getContent(), true);
        $url .= '?username=' . $data['name'];

        return $this->httpRequest($url, null, $parameters, array(), 'GET');
    }
}

