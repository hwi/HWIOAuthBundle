Bonus: Custom Queries
===

This bundle is mainly for authentication, but you can get custom information from the ResourceOwners.

#Example

```php
$accessToken = $request->getSession()->get('linkedin_access_token');

if ($accessToken === null) {
    $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));

    $resourceOwner = $ownerMap->getResourceOwnerByName('linkedin');

    if ($resourceOwner === null) {
        throw new \RuntimeException(sprintf("No resource owner with name '%s'.", 'linkedin'));
    }

    if (!$resourceOwner->handles($request)) {
        return new RedirectResponse(
             $this->container->get('hwi_oauth.security.oauth_utils')->getAuthorizationUrl('linkedin', $this->generateUrl('your_route_name', array(), true));
        );
    }

    $accessToken = $resourceOwner->getAccessToken(
        $request,
        $this->generateUrl('your_route_name', array(), true)
    );

    $request->getSession()->set('linkedin_access_token', $accessToken);
}

if ($accessToken !== null) {
    $ownerMap = $this->container->get('hwi_oauth.resource_ownermap.'.$this->container->getParameter('hwi_oauth.firewall_name'));

    $resourceOwner = $ownerMap->getResourceOwnerByName('linkedin');
    $url = 'https://api.linkedin.com/v1/people/~:(industry)?format=json';

    $response = $resourceOwner->getCustomInformation($accessToken, $url)->getResponse();
}
```

The `$response` variable will contain the data returned by the provider.