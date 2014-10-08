<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * VendResourceOwner
 */
class VendResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'domain_prefix',
        'realname'   => 'name',
        'email'      => 'domain_prefix'
    );

    /**
     * @var string Retailer name
     */
    protected $retailer;

    /**
     * @param $retailer
     */
    public function setRetailer($retailer)
    {
        $this->options['retailer'] = $retailer;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest(sprintf($url, $this->options['retailer']), http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest(sprintf($url, $this->options['retailer']));
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
                'authorization_url'        => 'https://secure.vendhq.com/connect',
                'access_token_url'         => 'https://%s.vendhq.com/api/1.0/token',
                'infos_url'                => 'https://%s.vendhq.com/api/2.0/retailer',
                'retailer'                 => null
            ));
    }
}