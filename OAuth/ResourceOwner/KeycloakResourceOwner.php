<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * KeycloakResourceOwner.
 *
 * @author Andrea Quintino <andreaquin1990@gmail.com>
 */
class KeycloakResourceOwner extends GenericOAuth2ResourceOwner
{
    public function configure()
    {
        $this->prepareKeycloakUrls();
    }

    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge([
          'approval_prompt' => $this->getOption('approval_prompt'),
        ], $extraParameters));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
          ->setRequired(['base_url', 'realm'])
          ->setDefaults([
            'protocol'          => 'openid-connect',
            'scope'             => 'openid email',
            'response_type'     => 'code',
            'approval_prompt'   => 'auto',
            'authorization_url' => null,
            'access_token_url'  => null,
            'infos_url'         => null,
           ]);
    }

    protected function prepareKeycloakUrls()
    {
        $baseAuthUrl = trim($this->getOption('base_url'), '/');

        $baseAuthUrl .= '/realms/'.$this->getOption('realm');
        $baseAuthUrl .= '/protocol/'.$this->getOption('protocol');

        $this->options['authorization_url'] = $baseAuthUrl.'/auth';
        $this->options['access_token_url']  = $baseAuthUrl.'/token';
        $this->options['infos_url']         = $baseAuthUrl.'/userinfo';
    }
}
