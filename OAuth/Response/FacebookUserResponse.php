<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

class FacebookUserResponse extends PathUserResponse
{

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return 'https://graph.facebook.com/'.$this->getValueForPath('identifier').'/picture?width=600&height=600';
    }
}
