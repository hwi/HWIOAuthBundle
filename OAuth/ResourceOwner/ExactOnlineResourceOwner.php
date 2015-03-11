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
 * ExactOnlineResourceOwner
 *
 * @author Martijn Gastkemper <martijngastkemper@gmail.com>
 */
class ExactOnlineResourceOwner extends GenericOAuth2ResourceOwner
{

	protected function doGetUserInformationRequest( $url, array $parameters = array() )
	{
		return $this->httpRequest( $url, null, ['Content-Type: application/json', 'Accept: application/json' ] );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function configureOptions( OptionsResolverInterface $resolver )
	{
		parent::configureOptions( $resolver );

		$resolver->setDefaults( array(
			'authorization_url' => 'https://start.exactonline.nl/api/oauth2/auth',
			'access_token_url' => 'https://start.exactonline.nl/api/oauth2/token',
			'infos_url' => 'https://start.exactonline.nl/api/v1/current/Me',
			'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\ExactOnlineUserResponse',
			'scope' => 'code',
			'use_bearer_authorization' => false,
		) );
	}

}
