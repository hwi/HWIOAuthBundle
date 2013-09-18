<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;

class OAuthUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValidData
     */
    public function testSignatureIsGeneratedCorrectly($signature, $url)
    {
        // Parameters from http://oauth.net/core/1.0a/#anchor46
        $parameters = array(
            'oauth_consumer_key'     => 'dpf43f3p2l4k3l03',
            'oauth_token'            => 'nnch734d00sl2jdk',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp'        => '1191242096',
            'oauth_nonce'            => 'kllo9940pd9333jh',
            'oauth_version'          => '1.0',
        );

        $this->assertEquals(
            $signature,
            OAuthUtils::signRequest('GET', $url, $parameters, 'kd94hf93k423kf44', 'pfkkdhi9sl3r4s00')
        );
    }

    /**
     * @dataProvider provideInvalidData
     * @expectedException \RuntimeException
     */
    public function testThrowsExceptionIfRequiredParameterIsMissing($parameters)
    {
        OAuthUtils::signRequest('GET', 'http://example.com', $parameters, 'client_secret');
    }

    public function provideValidData()
    {
        return array(
            array('iflJZCKxEsZ58FFDyCysxfLbuKM=', 'http://photos.example.net/photos'),
            array('tR3+Ty81lMeYAr/Fid0kMTYa/WM=', 'http://photos.example.net/photos?file=vacation.jpg&size=original'),
        );
    }

    public function provideInvalidData()
    {
        return array(
            array('oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_nonce' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_version' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_signature_method' => ''),
            array('oauth_consumer_key' => '', 'oauth_timestamp' => '', 'oauth_nonce' => '', 'oauth_version' => ''),
        );
    }
}