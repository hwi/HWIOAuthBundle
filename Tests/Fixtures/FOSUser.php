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

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @author Alexander <iam.asm89@gmail.com>
 * @ORM\Entity
 */
class FOSUser extends BaseUser
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleId;

    private $githubId;

    public function __construct()
    {
        parent::__construct();

        $this->username = 'foo';
        $this->email = 'foo@bar.com';
        $this->password = 'secret';
        $this->enabled = true;
    }

    public function getId()
    {
        return 1;
    }

    public function getUsername()
    {
        return 'foo';
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return 'secret';
    }

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

    public function getGoogleId()
    {
        return $this->googleId;
    }

    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
    }
}
