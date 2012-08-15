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

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class parsing the properties by given path options.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class PathUserResponse extends AbstractUserResponse
{
    /**
     * @var array
     */
    protected $paths = array();

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getValueForPath('identifier');
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
    public function getRealName()
    {
        return $this->getValueForPath('realname');
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
     *
     * @param array $paths
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * @param $name
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    protected function getPath($name)
    {
        if (!isset($this->paths[$name])) {
            throw new AuthenticationException(sprintf('No path with name "%s" configured.', $name));
        }

        return $this->paths[$name];
    }

    /**
     * Extracts a value from the response for a given path.
     *
     * @param string  $path           Name of the path to get the value for
     * @param boolean $catchException Whether to throw an exception or return null
     *
     * @return null|string
     *
     * @throws AuthenticationException
     */
    protected function getValueForPath($path, $throwException = true)
    {
        try {
            $steps = explode('.', $this->getPath($path));
        } catch (AuthenticationException $e) {
            if (!$throwException) {
                return null;
            }

            throw $e;
        }

        $value = $this->response;
        foreach ($steps as $step) {
            if (!array_key_exists($step, $value)) {
                if (!$throwException) {
                    return null;
                }

                throw new AuthenticationException(sprintf('Could not follow path "%s" in OAuth provider response: %s', $this->paths[$path], var_export($this->response, true)));
            }
            $value = $value[$step];
        }

        return $value;
    }
}
