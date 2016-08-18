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
 * Bitbucket2ResourceOwner
 *
 * @author David Sanchez <david38sanchez@gmail.com>
 */
class Bitbucket2ResourceOwner extends GenericOAuth2ResourceOwner
{

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'uuid',
        'nickname'       => 'username',
        'email'          => 'email',
        'realname'       => 'display_name',
        'profilepicture' => 'links.avatar.href',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://bitbucket.org/site/oauth2/authorize',
            'access_token_url'  => 'https://bitbucket.org/site/oauth2/access_token',
            'infos_url'         => 'https://api.bitbucket.org/2.0/user',
            'emails_url'        => 'https://api.bitbucket.org/2.0/user/emails'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $response = parent::getUserInformation($accessToken, $extraParameters);
        $responseData = $response->getResponse();

        // fetch the email addresses linked to the account
        if (empty($responseData['email'])) {
            $content = $this->httpRequest($this->normalizeUrl($this->options['emails_url']), null, array('Authorization: Bearer '.$accessToken['access_token']));
            foreach ($this->getResponseContent($content)['values'] as $email) {
                // we only need the primary email address
                if (true === $email['is_primary']) {
                    $responseData['email'] = $email['email'];
                }
            }
            $response->setResponse($responseData);
        }

        return $response;
    }
}
