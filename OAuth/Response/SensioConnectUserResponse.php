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
 * SensioUserResponse
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 * @author SensioLabs <contact@sensiolabs.com>
 */
class SensioConnectUserResponse extends AbstractUserResponse implements AdvancedUserResponseInterface
{
    /**
     * @var \DOMXpath
     */
    private $xpath;

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getValueForPath('login');
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayName()
    {
        return $this->getValueForPath('name');
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
        return $this->getValueForPath('avatar_url');
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse($response)
    {
        $dom = new \DOMDocument();
        try {
            if (!$dom->loadXML($response)) {
                throw new \ErrorException('Could not transform this xml to a \DOMDocument instance.');
            };
        } catch(\Exception $e) {
            throw new AuthenticationException('Could not retrieve valid user info.');
        }
        $this->xpath = new \DOMXpath($dom);

        $nodes = $this->xpath->evaluate('/api/root');
        $user  = $this->xpath->query('./foaf:Person', $nodes->item(0));
        if (1 !== $user->length) {
            throw new AuthenticationException('Could not retrieve user info.');
        }

        $element  = $user->item(0);
        $username = $this->getNodeValue('./foaf:name', $element);

        $this->response = array(
            'login'      => $username,
            'name'       => $username,
            'email'      => $this->getNodeValue('./foaf:mbox', $element),
            'avatar_url' => $this->getLinkToFoafDepiction($element),
        );
    }

    /**
     * @param string $path
     *
     * @return string|null
     */
    protected function getValueForPath($path)
    {
        if (isset($this->response[$path])) {
            return $this->response[$path];
        }

        return null;
    }

    /**
     * @param \DOMElement $element
     *
     * @return mixed|null
     */
    private function getLinkToFoafDepiction(\DOMElement $element)
    {
        $nodeList = $this->xpath->query('./atom:link[@rel="foaf:depiction"]', $element);
        if ($nodeList && $nodeList->length > 0) {
            return $this->sanitizeValue($nodeList->item(0)->attributes->getNamedItem('href')->value);
        }

        return null;
    }

    /**
     * @param string      $query
     * @param \DOMElement $element
     *
     * @return mixed|null
     */
    private function getNodeValue($query, \DOMElement $element)
    {
        $nodeList = $this->xpath->query($query, $element);
        if ($nodeList && $nodeList->length > 0) {
            return $this->sanitizeValue($nodeList->item(0)->nodeValue);
        }

        return null;
    }

    /**
     * @param string $value
     *
     * @return mixed
     */
    private function sanitizeValue($value)
    {
        if ('true' === $value) {
            $value = true;
        } elseif ('false' === $value) {
            $value = false;
        } elseif (empty($value)) {
            $value = null;
        }

        return $value;
    }
}
