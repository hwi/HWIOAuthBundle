<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * SecurityController
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class SecurityController extends ContainerAware
{
    /**
     * Login action showing links to all the resource owners. defined for a
     * firewall.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function loginAction(Request $request)
    {
        if (!$this->container->getParameter('hwi_oauth.firewall_name')) {
            throw new \RuntimeException('A firewall_name should be configured in order to use the default login action.');
        }

        $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));
        $httpUtils = $this->container->get('security.http_utils');

        $resourceOwners = array();
        foreach ($ownerMap->getResourceOwners() as $resourceOwnerInfo) {
            $resourceOwner = $this->container->get($resourceOwnerInfo['service']);
            $resourceOwners[] = array(
                'url' => $resourceOwner->getAuthorizationUrl($this->getUriForCheckPath($request, $resourceOwnerInfo['check_path'])),
                'name' => $resourceOwnerInfo['service'],
            );
        }

        $session = $request->getSession();

        // code adapted from FOSUB: https://github.com/FriendsOfSymfony/FOSUserBundle/blob/2c7721/Controller/SecurityController.php
        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Security:login.html.twig', array(
            'error'         => $error,
            'resource_owners' => $resourceOwners,
        ));
    }

    /**
     * Get the uri for a given path.
     *
     * @param Request $request A request instance
     * @param string  $path    Path or route
     *
     * @return string
     */
    private function getUriForCheckPath(Request $request, $path)
    {
        if ($path && '/' !== $path[0] && 0 !== strpos($path, 'http')) {
            $path = $this->container('router')->generate($path, array(), true);
        }

        if (0 !== strpos($path, 'http')) {
            $path = $request->getUriForPath($path);
        }

        return $path;
    }
}
