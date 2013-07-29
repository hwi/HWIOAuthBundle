<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Firewall;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

/**
 * OAuthListener
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthListener extends AbstractAuthenticationListener
{
    /**
     * @var ResourceOwnerMap
     */
    private $resourceOwnerMap;

    /**
     * @var array
     */
    private $checkPaths;

    /**
     * @var ResourceOwnerMap $resourceOwnerMap
     */
    public function setResourceOwnerMap(ResourceOwnerMap $resourceOwnerMap)
    {
        $this->resourceOwnerMap = $resourceOwnerMap;
    }

    /**
     * @param array $checkPaths
     */
    public function setCheckPaths(array $checkPaths)
    {
        $this->checkPaths = $checkPaths;
    }

    /**
     * {@inheritDoc}
     */
    public function requiresAuthentication(Request $request)
    {
        // Check if the route matches one of the check paths
        foreach ($this->checkPaths as $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function attemptAuthentication(Request $request)
    {
        $this->handleOAuthError($request);

        list($resourceOwner, $checkPath) = $this->resourceOwnerMap->getResourceOwnerByRequest($request);

        /* @var \HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $resourceOwner */
        if (!$resourceOwner) {
            throw new AuthenticationException('No resource owner match the request.');
        }

        if (!$resourceOwner->handles($request)) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        $resourceOwner->isCsrfTokenValid($request->get('state'));

        $accessToken = $resourceOwner->getAccessToken(
            $request,
            $this->httpUtils->createRequest($request, $checkPath)->getUri()
        );

        $token = new OAuthToken($accessToken);
        $token->setResourceOwnerName($resourceOwner->getName());

        return $this->authenticationManager->authenticate($token);
    }

    /**
     * @param Request $request
     *
     * @throws AuthenticationException
     */
    private function handleOAuthError(Request $request)
    {
        $error = null;

        // Try to parse content if error was not in request query
        if ($request->query->has('error')) {
            $content = json_decode($request->getContent(), true);
            if (JSON_ERROR_NONE === json_last_error() && isset($content['error'])) {
                if (isset($content['error']['message'])) {
                    throw new AuthenticationException($content['error']['message']);
                }

                if (isset($content['error']['code'])) {
                    $error = $content['error']['code'];
                } elseif (isset($content['error']['error-code'])) {
                    $error = $content['error']['error-code'];
                } else {
                    $error = $request->query->get('error');
                }
            }
        } elseif ($request->query->has('oauth_problem')) {
            $error = $request->query->get('oauth_problem');
        }

        if (null !== $error) {
            throw new AuthenticationException($this->transformOAuthError($error));
        }
    }

    /**
     * Transforms OAuth error codes into human readable format
     *
     * @param string $errorCode
     *
     * @return string
     */
    private function transformOAuthError($errorCode)
    {
        // "translate" error to human readable format
        switch ($errorCode) {
            case 'access_denied':
                $error = 'You have refused access for this site.';
                break;

            case 'authorization_expired':
                $error = 'Authorization expired.';
                break;

            case 'bad_verification_code':
                $error = 'Bad verification code.';
                break;

            case 'consumer_key_rejected':
                $error = 'You have refused access for this site.';
                break;

            case 'incorrect_client_credentials':
                $error = 'Incorrect client credentials.';
                break;

            case 'invalid_assertion':
                $error = 'Invalid assertion.';
                break;

            case 'redirect_uri_mismatch':
                $error = 'Redirect URI mismatches configured one.';
                break;

            case 'unauthorized_client':
                $error = 'Unauthorized client.';
                break;

            case 'unknown_format':
                $error = 'Unknown format.';
                break;

            default:
                $error = sprintf('Unknown OAuth error: "%s".', $errorCode);
        }

        return $error;
    }
}
