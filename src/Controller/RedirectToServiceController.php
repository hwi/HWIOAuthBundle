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

use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use HWI\Bundle\OAuthBundle\Util\DomainWhitelist;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @internal
 */
final class RedirectToServiceController
{
    private OAuthUtils $oauthUtils;
    private DomainWhitelist $domainWhitelist;
    private ResourceOwnerMapLocator $resourceOwnerMapLocator;
    private ?string $targetPathParameter;
    private bool $failedUseReferer;
    private bool $useReferer;

    public function __construct(
        OAuthUtils $oauthUtils,
        DomainWhitelist $domainWhitelist,
        ResourceOwnerMapLocator $resourceOwnerMapLocator,
        ?string $targetPathParameter,
        bool $failedUseReferer,
        bool $useReferer
    ) {
        $this->oauthUtils = $oauthUtils;
        $this->domainWhitelist = $domainWhitelist;
        $this->resourceOwnerMapLocator = $resourceOwnerMapLocator;
        $this->targetPathParameter = $targetPathParameter;
        $this->failedUseReferer = $failedUseReferer;
        $this->useReferer = $useReferer;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function redirectToServiceAction(Request $request, string $service): RedirectResponse
    {
        try {
            $authorizationUrl = $this->oauthUtils->getAuthorizationUrl($request, $service);
        } catch (\RuntimeException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $this->storeReturnPath($request, $authorizationUrl);

        return new RedirectResponse($authorizationUrl);
    }

    private function storeReturnPath(Request $request, string $authorizationUrl): void
    {
        try {
            $session = $request->getSession();
        } catch (SessionNotFoundException $e) {
            return;
        }

        $param = $this->targetPathParameter;

        foreach ($this->resourceOwnerMapLocator->getFirewallNames() as $firewallName) {
            $sessionKey = '_security.'.$firewallName.'.target_path';
            $sessionKeyFailure = '_security.'.$firewallName.'.failed_target_path';

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
