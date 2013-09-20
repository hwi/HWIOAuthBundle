Bonus: Custom Queries
=======================

This bundle is mainly for authentication, but you can get custom information from the ResourceOwners.

#Example

        $accessToken = $request->getSession()->get('linkedin_access_token');

        if ($accessToken === null) {
            if ($request->get('code')) {
                $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));

                $resourceOwner = $ownerMap->getResourceOwnerByName('linkedin');

                if ($resourceOwner !== null && $resourceOwner->handles($request)) {
                    $accessToken = $resourceOwner->getAccessToken(
                        $request,
                        $this->generateUrl('hrm_project_worktime_index', array(), true)
                    );

                    $request->getSession()->set('linkedin_access_token', $accessToken);
                }
            }
            else {
                return new RedirectResponse(
                    $this->container->get('hwi_oauth.security.oauth_utils')->getAuthorizationUrl(
                            'linkedin', $this->generateUrl('hrm_project_worktime_index', array(), true))
                );
            }
        }

        if ($accessToken !== null) {
            $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));

            $resourceOwner = $ownerMap->getResourceOwnerByName('linkedin');
            $url = 'https://api.linkedin.com/v1/people/~:(industry)?format=json';

            $response = $resourceOwner->getCustomInformation($accessToken, $url)->getResponse();
        }

The `$response` variable will contain the data returned by the provider.