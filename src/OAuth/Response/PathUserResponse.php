<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

/**
 * Class parsing the properties by given path options.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class PathUserResponse extends AbstractUserResponse
{
    /**
     * @var array<string, int|string|null>
     */
    protected $paths = [
        'identifier' => null,
        'nickname' => null,
        'firstname' => null,
        'lastname' => null,
        'realname' => null,
        'email' => null,
        'profilepicture' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        $value = $this->getValueForPath('identifier');
        if (null === $value) {
            throw new \InvalidArgumentException('User identifier was not found in response.');
        }

        return (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        try {
            return $this->getUserIdentifier();
        } catch (\InvalidArgumentException $e) {
            // @phpstan-ignore-next-line BC compatibility
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        return $this->getValueForPath('nickname');
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->getValueForPath('firstname');
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->getValueForPath('lastname');
    }

    /**
     * {@inheritdoc}
     */
    public function getRealName()
    {
        return $this->getValueForPath('realname');
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getValueForPath('email');
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return $this->getValueForPath('profilepicture');
    }

    /**
     * Get the configured paths.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Configure the paths.
     */
    public function setPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
    }

    /**
     * @param string $name
     *
     * @return array|string|null
     */
    public function getPath($name)
    {
        return $this->paths[$name] ?? null;
    }

    /**
     * Extracts a value from the response for a given path.
     *
     * @param string $path Name of the path to get the value for
     *
     * @return string|null
     */
    protected function getValueForPath($path)
    {
        $data = $this->data;
        if (!$data) {
            return null;
        }

        $steps = $this->getPath($path);
        if (!$steps) {
            return null;
        }

        if (\is_array($steps)) {
            if (1 === \count($steps)) {
                return $this->getValue(current($steps), $data);
            }

            $value = [];
            foreach ($steps as $step) {
                $value[] = $this->getValue($step, $data);
            }

            return trim(implode(' ', $value)) ?: null;
        }

        return $this->getValue($steps, $data);
    }

    /**
     * @return array|string|null
     */
    private function getValue(string $steps, array $data)
    {
        $value = $data;
        foreach (explode('.', $steps) as $step) {
            if (!\array_key_exists($step, $value)) {
                return null;
            }

            $value = $value[$step];
        }

        return $value;
    }
}
