<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthAuthenticationException extends AuthenticationException
{
    private $messageKey;
    private $messageData = array();

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return $this->messageKey;
    }

    public function setMessageKey($messageKey)
    {
        // "translate" error to human readable format
        switch ($messageKey) {
            case 'access_denied':
                $messageKey = 'You have refused access for this site.';
                break;

            case 'authorization_expired':
                $messageKey = 'Authorization expired.';
                break;

            case 'bad_verification_code':
                $messageKey = 'Bad verification code.';
                break;

            case 'not_a_valid_access_token':
                $messageKey = 'Not a valid access token.';
                break;

            case 'consumer_key_rejected':
                $messageKey = 'You have refused access for this site.';
                break;

            case 'incorrect_client_credentials':
                $messageKey = 'Incorrect client credentials.';
                break;

            case 'invalid_assertion':
                $messageKey = 'Invalid assertion.';
                break;

            case 'redirect_uri_mismatch':
                $messageKey = 'Redirect URI mismatches configured one.';
                break;

            case 'unauthorized_client':
                $messageKey = 'Unauthorized client.';
                break;

            case 'unknown_format':
                $messageKey = 'Unknown format.';
                break;

            case 'callback_not_confirmed':
                $messageKey = 'OAuth callback was not confirmed.';
                break;

            default:
                $messageKey = 'Unknown OAuth error: "%error%".';
                break;
        }

        $this->messageKey = $messageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData()
    {
        return $this->messageData;
    }

    public function setMessageData(array $messageData)
    {
        $this->messageData = $messageData;
    }
}
