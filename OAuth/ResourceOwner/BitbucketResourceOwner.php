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
 * BitbucketResourceOwner
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class BitbucketResourceOwner extends GenericOAuth1ResourceOwner
{
    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'user.username',
        'nickname'       => 'user.username',
        'realname'       => 'user.display_name',
        'profilepicture' => 'user.avatar',
    );

    /**
     * {@inheritDoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'authorization_url' => 'https://bitbucket.org/api/1.0/oauth/authenticate',
            'request_token_url' => 'https://bitbucket.org/api/1.0/oauth/request_token',
            'access_token_url'  => 'https://bitbucket.org/api/1.0/oauth/access_token',
            'infos_url'         => 'https://bitbucket.org/api/1.0/user',
			'emails_url'		=> 'https://bitbucket.org/api/1.0/users/%s/emails'
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
			$content = $this->httpRequest($this->normalizeUrl(
				sprintf($this->options['emails_url'], $responseData['username'])
			), null, array('Authorization: Bearer '.$accessToken['access_token']));

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
