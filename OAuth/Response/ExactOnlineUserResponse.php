<?php

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

/**
 * @author Martijn Gastkemper <martijngastkemper@gmail.com>
 */
class ExactOnlineUserResponse extends \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
{

	public function getNickname()
	{
		return $this->response[ 'UserName' ];
	}

	public function getRealName()
	{
		return $this->response[ 'FullName' ];
	}

	public function getUsername()
	{
		return $this->response[ 'UserID' ];
	}
	
	public function getEmail()
	{
		return $this->response['Email'];
	}
	
	public function getProfilePicture()
	{
		return $this->response['PictureUrl'];
	}
	
	public function getDivision() 
	{
		return $this->response['CurrentDivision'];
	}

	public function setResponse( $response )
	{
		$this->response = ( array ) json_decode( $response )->d->results[ 0 ];
	}

}
