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
        'identifier' => 'data.id',
        'nickname'   => 'data.name',
        'realname'   => 'data.name',
        'email'      => 'data.domain_prefix'
    );

    /**
     * @param $domainPrefix
     */
    public function setDomainPrefix($domainPrefix)
    {
        $this->options['domain_prefix'] = $domainPrefix;
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest(sprintf($url, $this->options['domain_prefix']), http_build_query($parameters, '', '&'));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $this->options['infos_url'] = sprintf($this->options['infos_url'], $this->options['domain_prefix']);

        return parent::getUserInformation($accessToken, $extraParameters);
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
