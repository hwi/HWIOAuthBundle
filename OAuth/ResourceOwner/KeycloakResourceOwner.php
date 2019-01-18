<?php
/**
 * Created by PhpStorm.
 * User: andreaquintino
 * Date: 17/01/19
 * Time: 17.18
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

    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        return parent::getAuthorizationUrl($redirectUri, array_merge([
          'approval_prompt' => $this->getOption('approval_prompt')
        ],$extraParameters));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
          ->setDefined(['protocol', 'response_type', 'approval_prompt'])
          ->setRequired('realms')
          ->setDefaults([
            'protocol' => 'openid-connect',
            'scope' =>  'name,email',
            'response_type' => 'code',
            'approval_prompt' => 'auto'
          ]);
    }

    protected function prepareBaseAuthenticationUrl()
    {
        $baseAuthUrl = trim($this->getOption('authorization_url'), '/');
        //check if already configured
        if(strpos($baseAuthUrl, '/realms') !== false) {
            return;
        }

        $baseAuthUrl .= '/realms/'.$this->getOption('realms');
        $baseAuthUrl .= '/protocol/'.$this->getOption('protocol').'/auth';

        $this->options['authorization_url'] = $baseAuthUrl;

    }
}