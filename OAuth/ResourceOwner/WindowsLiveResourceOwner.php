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

use Symfony\Component\OptionsResolver\OptionsResolver;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Psr\Http\Message\ResponseInterface;

/**
 * WindowsLiveResourceOwner.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class WindowsLiveResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritdoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'emails.account', // requires 'wl.emails' scope
    );

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://login.live.com/oauth20_authorize.srf',
            'access_token_url' => 'https://login.live.com/oauth20_token.srf',
            'infos_url' => 'https://apis.live.net/v5.0/me',

            'scope' => 'wl.signin',
        ));
    }

    /**
     * @inheritdoc
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest(
                $this->normalizeUrl($this->options['infos_url'], $extraParameters),
                null,
                array(
                    'Authorization' => 'Bearer '.$accessToken['access_token'],
                    'Content-Type' => null,
                )
            );
        } else {
            $content = $this->doGetUserInformationRequest(
                $this->normalizeUrl(
                    $this->options['infos_url'],
                    array_merge(array($this->options['attr_name'] => $accessToken['access_token']), $extraParameters)
                )
            );
        }

        $response = $this->getUserResponse();
        $response->setData($content instanceof ResponseInterface ? (string) $content->getBody() : $content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));
        return $response;
    }
}
