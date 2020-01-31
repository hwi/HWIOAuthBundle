<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

if (method_exists(AbstractToken::class, '__serialize')) {
    // Symfony >= 4.3
    class OAuthToken extends AbstractOAuthToken
    {
    }
} else {
    // Symfony 3.4
    class OAuthToken extends AbstractOAuthToken
    {
        /**
         * {@inheritdoc}
         */
        public function serialize()
        {
            return serialize($this->__serialize());
        }

        /**
         * {@inheritdoc}
         */
        public function unserialize($serialized)
        {
            $data = unserialize($serialized);

            $this->__unserialize($data);
        }
    }
}
