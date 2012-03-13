<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

/**
 * OAuthAwareExceptionInterface
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
interface OAuthAwareExceptionInterface
{
    /**
    * Set the access token of the failed authentication request.
    *
    * @param string $accessToken
    */
    public function setAccessToken($accessToken);

    /**
    * @return string
    */
    public function getAccessToken();

    /**
    * Set the name of the resource owner responsible for the oauth authentication.
    *
    * @param string $resourceOwnerName
    */
    public function setResourceOwnerName($resourceOwnerName);

    /**
    * @return string
    */
    public function getResourceOwnerName();
}
