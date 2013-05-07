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

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

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
        if (in_array($name, array('authorization_url', 'request_token_url', 'access_token_url', 'infos_url'))) {
            return str_replace('{base_url}', $this->getOption('base_url'), parent::getOption($name));
        }

        return parent::getOption($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        $response = $this->httpRequest($this->getOption('base_url').'/rest/auth/1/session', null, $parameters, array(), 'GET');
        $data     = json_decode($response->getContent(), true);

        return $this->httpRequest($this->normalizeUrl($url, array('username' => $data['name'])), null, $parameters, array(), 'GET');
    }
}

