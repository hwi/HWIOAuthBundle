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
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
final class GitHubResourceOwner extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'github';

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'login',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $response = parent::getUserInformation($accessToken, $extraParameters);

        $responseData = $response->getData();
        if (empty($responseData['email'])) {
            // fetch the email addresses linked to the account
            $content = $this->httpRequest(
                $this->normalizeUrl($this->options['emails_url']), null, ['Authorization' => 'Bearer '.$accessToken['access_token']]
            );

            foreach ($this->getResponseContent($content) as $email) {
                if (!empty($email['primary'])) {
                    // we only need the primary email address
                    $responseData['email'] = $email['email'];
                    break;
                }
            }

            $response->setData($responseData);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        $response = $this->httpRequest(
            sprintf($this->options['revoke_token_url'], $this->options['client_id']),
            json_encode(['access_token' => $token]),
            [
                'Authorization' => 'Basic '.base64_encode($this->options['client_id'].':'.$this->options['client_secret']),
                'Content-Type' => 'application/json',
            ],
            'DELETE'
        );

        return 204 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => 'https://github.com/login/oauth/authorize',
            'access_token_url' => 'https://github.com/login/oauth/access_token',
            'revoke_token_url' => 'https://api.github.com/applications/%s/token',
            'infos_url' => 'https://api.github.com/user',
            'emails_url' => 'https://api.github.com/user/emails',

            'use_commas_in_scope' => true,
        ]);
    }
}
