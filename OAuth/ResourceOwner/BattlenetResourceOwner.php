<?php

/*
 * This file extend the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BattlenetResourceOwner.
 *
 * @author Jeremy Pouillot <pouillotjeremy@gmail.com>
 */
class BattlenetResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'battleTag' => 'battletag',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://eu.battle.net/oauth/authorize',
            'access_token_url' => 'https://eu.battle.net/oauth/token',
            'infos_url' => 'https://eu.api.battle.net/account/user',
            'characters_url' => 'https://eu.api.battle.net/wow/user/characters'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = array(
                $this->httpRequest($this->normalizeUrl($this->options['infos_url'], $extraParameters), null, array('Authorization: Bearer '.$accessToken['access_token']))->getContent(),
                $accessToken
            );
        } else {
            $content = $this->doGetUserInformationRequest($this->normalizeUrl($this->options['infos_url'], array_merge(array($this->options['attr_name'] => $accessToken['access_token']), $extraParameters)));
        }

        $response = $this->getUserResponse();
        $response->setData($content);

        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }
}
