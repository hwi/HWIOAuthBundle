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

use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\LazyResponseException;

/**
 * @author zorn-v
 */
class Telegram extends GenericOAuth2ResourceOwner
{
    public const TYPE = 'telegram';

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'first_name',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'profilepicture' => 'photo_url',
    ];

    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        list($botId) = explode(':', $this->options['client_secret']);
        $parameters = array_merge([
            'bot_id' => $botId,
            'origin' => $redirectUri,
            'return_to' => $redirectUri,
        ], $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    public function handles(Request $request)
    {
        if (!$request->query->has('code')) {
            $js = sprintf('<script>location.href = "?code=" + new URLSearchParams(location.hash.substring(1)).get("tgAuthResult")</script>');
            throw new LazyResponseException(new Response($js));
        }
        return true;
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        $token = $request->query->get('code');
        $data = json_decode(base64_decode($token), true);
        if (!$data) {
            throw new AuthenticationException('Missing Telegram data');
        }
        $authData = $data;
        $botToken = $this->options['client_secret'];
        $checkHash = $authData['hash'];
        unset($authData['hash']);
        ksort($authData);
        array_walk($authData, function (&$value, $key) {$value = $key.'='.$value;});
        $dataCheckStr = implode("\n", $authData);
        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckStr, $secretKey);
        if ($hash !== $checkHash) {
            throw new AuthenticationException('Telegram auth data check failed');
        }
        if (empty($data['auth_date']) || (time() - $data['auth_date']) > 300) {
            throw new AuthenticationException('Telegram auth data expired');
        }

        return $token;
    }

    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        $data = base64_decode($accessToken['access_token']);
        $response = $this->getUserResponse();
        $response->setData($data);
        $response->setResourceOwner($this);

        return $response;
    }

    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'client_id',
            'client_secret',
            'authorization_url',
        ]);

        $resolver->setDefaults([
            'authorization_url' => 'https://oauth.telegram.org/auth',
            'auth_with_one_url' => true,
            'state' => null,
            'csrf' => false,
            'user_response_class' => PathUserResponse::class,
        ]);
    }
}
