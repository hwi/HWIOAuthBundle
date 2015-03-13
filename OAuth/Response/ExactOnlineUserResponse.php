<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Martijn Gastkemper <martijngastkemper@gmail.com>
 */
class ExactOnlineUserResponse extends PathUserResponse
{
	/**
     * {@inheritdoc}
     */
	public function setResponse( $response )
	{
		$json = json_decode($response);

		if (JSON_ERROR_NONE !== json_last_error()) {
			throw new AuthenticationException('Response is not a valid JSON code.');
		}
		
		$this->response = (array) current($json->d->results);
  }

}
