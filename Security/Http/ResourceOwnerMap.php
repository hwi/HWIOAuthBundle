<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * Holds several resource owners for a firewall. Lazy
 * loads the appropriate resource owner when requested.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
final class ResourceOwnerMap implements ResourceOwnerMapInterface
{
    private HttpUtils $httpUtils;
    private array $resourceOwners;
    private array $possibleResourceOwners;
    private ServiceLocator $locator;

    /**
     * @param array<string, string> $possibleResourceOwners array with possible resource owners names
     * @param array<string, string> $resourceOwners         array with configured resource owners
     */
    public function __construct(
        HttpUtils $httpUtils,
        array $possibleResourceOwners,
        array $resourceOwners,
        ServiceLocator $locator
    ) {
        $this->httpUtils = $httpUtils;
        $this->possibleResourceOwners = $possibleResourceOwners;
        $this->resourceOwners = $resourceOwners;
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function hasResourceOwnerByName(string $name): bool
    {
        return isset($this->resourceOwners[$name], $this->possibleResourceOwners[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerByName(string $name): ?ResourceOwnerInterface
    {
        if (!$this->hasResourceOwnerByName($name)) {
            return null;
        }

        try {
            /** @var ResourceOwnerInterface $resourceOwner */
            $resourceOwner = $this->locator->get($name);
        } catch (NotFoundExceptionInterface $e) {
            return null;
        }

        return $resourceOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerByRequest(Request $request): ?array
    {
        foreach ($this->resourceOwners as $name => $checkPath) {
            if ($this->httpUtils->checkRequestPath($request, $checkPath)) {
                $resourceOwner = $this->getResourceOwnerByName($name);

                // save the round-tripped state to the resource owner
                if (null !== $resourceOwner) {
                    $resourceOwner->storeState(new State($request->get('state'), false));
                }

                return [$resourceOwner, $checkPath];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerCheckPath(string $name): ?string
    {
        return $this->resourceOwners[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwners(): array
    {
        return $this->resourceOwners;
    }
}
