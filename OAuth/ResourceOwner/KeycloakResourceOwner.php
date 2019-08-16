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
        $this->prepareBaseAuthenticationUrl();
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
          ->setDefined(['protocol', 'response_type', 'approval_prompt'])
          ->setRequired('realms')
          ->setDefaults([
            'protocol' => 'openid-connect',
            'scope' => 'name,email',
            'response_type' => 'code',
            'approval_prompt' => 'auto',
          ]);
    }

    protected function prepareBaseAuthenticationUrl()
    {
        $baseAuthUrl = trim($this->getOption('authorization_url'), '/');
        //check if already configured
        if (false !== strpos($baseAuthUrl, '/realms')) {
            return;
        }

        $baseAuthUrl .= '/realms/'.$this->getOption('realms');
        $baseAuthUrl .= '/protocol/'.$this->getOption('protocol').'/auth';

        $this->options['authorization_url'] = $baseAuthUrl;
    }
}
