<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\InstagramResourceOwner;

class InstagramResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $paths = array(
        'identifier'      => 'user.id',
        'nickname'        => 'user.username',
        'realname'        => 'user.full_name',
        'profilepicture'  => 'user.profile_picture',
    );

    /**
     * Together with OAuth token Instagram sends user data.
     */
    public function testGetUserInformation()
    {
        $accessToken = array(
            'access_token' => 'token',
            'user'         => array(
                'id'              => '1574083',
                'username'        => 'snoopdogg',
                'full_name'       => 'Snoop Dogg',
                'profile_picture' => 'http://distillery.s3.amazonaws.com/profiles/profile_1574083_75sq_1295469061.jpg'
            )
        );
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1574083', $userResponse->getUsername());
        $this->assertEquals('snoopdogg', $userResponse->getNickname());
        $this->assertEquals('Snoop Dogg', $userResponse->getRealName());
        $this->assertEquals('http://distillery.s3.amazonaws.com/profiles/profile_1574083_75sq_1295469061.jpg', $userResponse->getProfilePicture());
        $this->assertEquals($accessToken['access_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('instagram', array('user_response_class' => $class));

        /* @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url' => 'https://api.instagram.com/oauth/authorize',
                'access_token_url'  => 'https://api.instagram.com/oauth/access_token',
            ),
            $options
        );

        return new InstagramResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
