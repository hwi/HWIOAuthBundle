<?php


namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\Response\IntuitResponseCrawler;
use HWI\Bundle\OAuthBundle\OAuth\Response\IntuitUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Exception\IntuitException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Buzz\Message\RequestInterface as HttpRequestInterface;

/**
 * Description for class IntuitResourceOwner
 * @author Volker von Hoesslin <volker.von.hoesslin@empora.com>
 */
class IntuitResourceOwner extends GenericOAuth1ResourceOwner {

    const OAUTH_REQUEST_URL = 'https://oauth.intuit.com/oauth/v1/get_request_token';
    const OAUTH_ACCESS_URL = 'https://oauth.intuit.com/oauth/v1/get_access_token';
    const OAUTH_AUTHORISE_URL = 'https://appcenter.intuit.com/Connect/Begin';
    const OAUTH_REFRESH_URL = 'https://appcenter.intuit.com/api/v1/connection/reconnect';
    const OAUTH_INFOS_URL = 'https://appcenter.intuit.com/api/v1/user/current';
    const INTUIT_RESPONSE_XML = 'xml';
    const INTUIT_RESPONSE_JSON = 'json';

    /**
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(OptionsResolverInterface $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'authorization_url' => self::OAUTH_AUTHORISE_URL,
            'request_token_url' => self::OAUTH_REQUEST_URL,
            'access_token_url' => self::OAUTH_ACCESS_URL,
            'infos_url' => self::OAUTH_INFOS_URL,
            'user_response_class' => IntuitUserResponse::class
        ]);
    }

    /**
     * @param mixed  $token
     * @param string $resourceUrl
     * @param string $requestBody
     * @param array  $extraParameters
     * @param string $httpMethod
     * @param string $responseType
     * @return string
     */
    public function fetchRequest($token, $resourceUrl, $requestBody = null, $extraParameters = [], $httpMethod = HttpRequestInterface::METHOD_GET, $responseType = self::INTUIT_RESPONSE_JSON) {
        /** @var OAuthToken $refreshToken */
        $accessToken = ($token instanceof OAuthToken) ? $token->getRawToken() : (array)$token;

        $parameters = array_merge([
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ], $extraParameters);

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            $httpMethod,
            $resourceUrl,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $headers = $this->createRequestHeader($resourceUrl, $responseType);

        $response = $this->httpRequest($resourceUrl, $requestBody, $parameters, $headers, $httpMethod);
        $content = $response->getContent();
        return $content;
    }

    /**
     * @param string $requestURI
     * @param string $responseType
     * @return array
     */
    protected function createRequestHeader($requestURI, $responseType = self::INTUIT_RESPONSE_JSON) {
        if (!in_array($responseType, [self::INTUIT_RESPONSE_JSON, self::INTUIT_RESPONSE_XML])) {
            throw new \InvalidArgumentException('Invalid response type');
        }
        $header = [
            'Host' => parse_url($requestURI, PHP_URL_HOST),
            'Accept' => 'application/' . $responseType,
            'Connection' => 'close',
            'Content-Type' => 'application/' . $responseType,
        ];
        return $header;
    }

    /**
     * @param string $refreshToken
     * @param array  $extraParameters
     * @return OAuthToken
     * @throws IntuitException
     * @see https://developer.intuit.com/docs/0050_quickbooks_api/0020_authentication_and_authorization/oauth_management_api#/Reconnect
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = []) {
        $content = $this->fetchRequest($refreshToken, self::OAUTH_REFRESH_URL, null, [], HttpRequestInterface::METHOD_GET, self::INTUIT_RESPONSE_XML);
        $crawler = new IntuitResponseCrawler($content);
        $errorCode = $crawler->filterXPath('ErrorCode')->text();
        if ($errorCode === '0') { //success
            return new OAuthToken([
                'access_token' => $crawler->filterXPath('OAuthToken')->text(),
                'oauth_token_secret' => $crawler->filterXPath('OAuthTokenSecret')->text()
            ]);
        } else { //error
            $message = $crawler->filterXPath('ErrorMessage')->text();
            throw new IntuitException($message, (int)$errorCode);
        }
    }


}