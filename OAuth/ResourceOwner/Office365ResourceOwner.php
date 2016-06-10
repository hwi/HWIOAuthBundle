<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;


use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Office365ResourceOwner extends GenericOAuth2ResourceOwner
{
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://login.microsoftonline.com/common/oauth2/authorize',
            'access_token_url' => 'https://login.microsoftonline.com/common/oauth2/token',
            'infos_url'         => 'https://graph.microsoft.com/v1.0/me'
        ));
    }

    /**
     * @inheritDoc
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        $extraParameters = array(
            'resource' => 'https://graph.microsoft.com'
        );

        return parent::getAccessToken($request, $redirectUri, $extraParameters);
    }


}