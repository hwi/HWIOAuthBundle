<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Controller;

use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Util\DomainWhitelist;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
final class RedirectToServiceController
{
    /**
     * @var OAuthUtils
     */
    private $oauthUtils;

    /**
     * @var DomainWhitelist
     */
    private $domainWhitelist;

    /**
     * @var array
     */
    private $firewallNames;

    /**
     * @var string
     */
    private $targetPathParameter;

    /**
     * @var bool
     */
    private $failedUseReferer;

    /**
     * @var bool
     */
    private $useReferer;

    public function __construct(OAuthUtils $oauthUtils, DomainWhitelist $domainWhitelist, array $firewallNames, ?string $targetPathParameter, bool $failedUseReferer, bool $useReferer)
    {
        $this->oauthUtils = $oauthUtils;
        $this->domainWhitelist = $domainWhitelist;
        $this->firewallNames = $firewallNames;
        $this->targetPathParameter = $targetPathParameter;
        $this->failedUseReferer = $failedUseReferer;
        $this->useReferer = $useReferer;
    }

    /**
     * @param Request $request
     * @param string  $service
     *
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function redirectToServiceAction(Request $request, $service): RedirectResponse
    {
        try {
            $authorizationUrl = $this->oauthUtils->getAuthorizationUrl($request, $service);
        } catch (RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $this->storeReturnPath($request, $authorizationUrl);

        return new RedirectResponse($authorizationUrl);
    }

    private function storeReturnPath(Request $request, string $authorizationUrl): void
    {
        $session = $request->getSession();

        if (null === $session) {
            return;
        }

        $param = $this->targetPathParameter;

        foreach ($this->firewallNames as $providerKey) {
            $sessionKey = '_security.'.$providerKey.'.target_path';
            $sessionKeyFailure = '_security.'.$providerKey.'.failed_target_path';

            if (!empty($param) && $targetUrl = $request->get($param)) {
                if (!$this->domainWhitelist->isValidTargetUrl($targetUrl)) {
                    throw new AccessDeniedHttpException('Not allowed to redirect to '.$targetUrl);
                }

                $session->set($sessionKey, $targetUrl);
            }

            if ($this->failedUseReferer && !$session->has($sessionKeyFailure) && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $authorizationUrl) {
                $session->set($sessionKeyFailure, $targetUrl);
            }

            if ($this->useReferer && !$session->has($sessionKey) && ($targetUrl = $request->headers->get('Referer')) && $targetUrl !== $authorizationUrl) {
                $session->set($sessionKey, $targetUrl);
            }
        }
    }
}
