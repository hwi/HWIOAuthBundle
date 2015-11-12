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

class SinaWeiboResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'screen_name',
        'realname'       => 'screen_name',
        'profilepicture' => 'profile_image_url',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken = null, array $extraParameters = array())
    {
        $url = $this->normalizeUrl($this->options['infos_url'], array(
            'access_token' => $accessToken['access_token'],
            'uid'          => $accessToken['uid'],
        ));

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
            'authorization_url' => 'https://api.weibo.com/oauth2/authorize',
            'access_token_url'  => 'https://api.weibo.com/oauth2/access_token',
            'infos_url'         => 'https://api.weibo.com/2/users/show.json',
        ));
    }
}
