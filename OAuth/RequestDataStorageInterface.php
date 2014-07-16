<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth;

/**
 * Interface for classes providing a request tokens storage.
 *
 * The storage is needed because the OAuth1.0a authentication flow requires
 * requests to be signed with the same values in consecutive requests.
 *
 * Additionally we require this to provide CSRF protection for all resource
 * owners.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
interface RequestDataStorageInterface
{
    /**
     * Fetch a request data from the storage.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $key
     * @param string                 $type
     *
     * @return array
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $key, $type = 'token');

    /**
     * Save a request data to the storage.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param array|string           $value
     * @param string                 $type
     */
    public function save(ResourceOwnerInterface $resourceOwner, $value, $type = 'token');
}
