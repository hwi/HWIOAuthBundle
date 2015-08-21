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

use Buzz\Message\RequestInterface as HttpRequestInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * GitHubResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class GitHubResourceOwner extends GenericOAuth2ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'login',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    /**
     * {@inheritDoc}
     */
    public function revokeToken($token)
    {
        $response = $this->httpRequest(
            sprintf($this->options['revoke_token_url'], $this->options['client_id'], $token),
            null,
            array('Authorization: Basic '.base64_encode($this->options['client_id'].':'.$this->options['client_secret'])),
            HttpRequestInterface::METHOD_DELETE
        );

        return 204 === $response->getStatusCode();
    }

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url'   => 'https://github.com/login/oauth/authorize',
            'access_token_url'    => 'https://github.com/login/oauth/access_token',
            'revoke_token_url'    => 'https://api.github.com/applications/%s/tokens/%s',
            'infos_url'           => 'https://api.github.com/user',
            'emails_url'          => 'https://api.github.com/user/emails',

            'use_commas_in_scope' => true,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $response = parent::getUserInformation($accessToken, $extraParameters);

        $responseData = $response->getResponse();

        if (empty($responseData['email'])) {
            // fetch the email addresses linked to the account
            $content = $this->httpRequest($this->normalizeUrl($this->options['emails_url']), null, array('Authorization: Bearer '.$accessToken['access_token']));

            foreach ($this->getResponseContent($content) as $email) {
                if (!empty($email['primary'])) {
                    // we only need the primary email address
                    $responseData['email'] = $email['email'];
                    break;
                }
            }

            $response->setResponse($responseData);
        }

        return $response;
    }
}
