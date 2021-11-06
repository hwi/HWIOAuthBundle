<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private $githubId;

    /**
     * @return string
     */
    public function getId()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return 'foo';
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return 'foo';
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return 'secret';
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return 'my_salt';
    }

    public function eraseCredentials()
    {
    }

    public function getGithubId()
    {
        return $this->githubId;
    }

    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }
}
