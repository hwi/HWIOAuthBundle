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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Salesforce Sandbox Resource Owner
 *
 * @author Matteo Rossi <orionerossi@libero.it>
 */
class SalesforceSandboxResourceOwner extends SalesforceResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://test.salesforce.com/services/oauth2/authorize',
            'access_token_url'  => 'https://test.salesforce.com/services/oauth2/token',

            // @see SalesforceResourceOwner::getUserInformation()
            'infos_url'         => null,

            // @see SalesforceResourceOwner::doGetUserInformationRequest()
            'format'            => 'json',
        ));
    }

}
