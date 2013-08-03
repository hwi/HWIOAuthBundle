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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * OdnoklassnikiResourceOwner
 *
 * @author Sergey Polischook <spolischook@gmail.com>
 */
class OdnoklassnikiResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $parameters = array(
            'access_token'    => $accessToken['access_token'],
            'application_key' => $this->options['application_key'],
            'sig'             => md5(sprintf('application_key=%smethod=users.getCurrentUser%s', $this->options['application_key'], md5($accessToken['access_token'].$this->options['client_secret']))),
        );
        $url = $this->normalizeUrl($this->options['infos_url'], $parameters);

        $content = $this->doGetUserInformationRequest($url)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'http://www.odnoklassniki.ru/oauth/authorize',
            'access_token_url'  => 'http://api.odnoklassniki.ru/oauth/token.do',
            'infos_url'         => 'http://api.odnoklassniki.ru/fb.do?method=users.getCurrentUser',

            'application_key'   => null,
        ));
    }
}
