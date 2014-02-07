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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

        /* @var ResourceOwnerInterface $resourceOwner */
        list($resourceOwner, $checkPath) = $this->resourceOwnerMap->getResourceOwnerByRequest($request);

        if (!$resourceOwner) {
            throw new AuthenticationException('No resource owner match the request.');
        }

        if (!$resourceOwner->handles($request)) {
            throw new AuthenticationException('No oauth code in the request.');
        }

        // If resource owner supports only one url authentication, call redirect
        if ($request->query->has('authenticated') && $resourceOwner->getOption('auth_with_one_url')) {
            $request->attributes->set('service', $resourceOwner->getName());

            return new RedirectResponse(sprintf('%s?code=%s&authenticated=true', $this->httpUtils->generateUri($request, 'hwi_oauth_connect_service'), $request->query->get('code')));
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
     * Detects errors returned by resource owners and transform them into
     * human readable messages
     *
     * @param Request $request
     *
     * @throws AuthenticationException
     */
    private function handleOAuthError(Request $request)
    {
        $error = null;

        // Try to parse content if error was not in request query
        if ($request->query->has('error') || $request->query->has('error_code')) {
            if ($request->query->has('error_description') || $request->query->has('error_message')) {
                throw new AuthenticationException(rawurldecode($request->query->get('error_description', $request->query->get('error_message'))));
            }

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
                return 'You have refused access for this site.';

            case 'authorization_expired':
                return 'Authorization expired.';

            case 'bad_verification_code':
                return 'Bad verification code.';

            case 'consumer_key_rejected':
                return 'You have refused access for this site.';

            case 'incorrect_client_credentials':
                return 'Incorrect client credentials.';

            case 'invalid_assertion':
                return 'Invalid assertion.';

            case 'redirect_uri_mismatch':
                return 'Redirect URI mismatches configured one.';

            case 'unauthorized_client':
                return 'Unauthorized client.';

            case 'unknown_format':
                return 'Unknown format.';
        }

        return sprintf('Unknown OAuth error: "%s".', $errorCode);
    }
}
